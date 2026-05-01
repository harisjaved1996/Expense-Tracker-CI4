# AI-Powered Sidebar Chat — End-to-End Technical Breakdown

---

## What This Feature Does (Plain English)

The user opens the chat sidebar and types a question like *"How much did I spend on Travel this month?"*. The PHP backend does NOT answer this itself. Instead it sends the question to an AI model (via OpenRouter API). The AI reads the question, decides it needs to query the database, calls the right tool, gets the data back, and then writes a human-friendly answer. That answer is sent back to the browser and shown in the sidebar.

---

## The Moving Parts

```
Browser (chat.js)
    ↓  POST /chat/send  {"message": "..."}
ChatController::send()   ← CI4 PHP
    ↓  POST https://openrouter.ai/api/v1/chat/completions
OpenRouter AI Model
    ↓  "call tool: get_expense_summary"
ChatController  ← executes the tool against MySQL
    ↓  POST https://openrouter.ai/api/v1/chat/completions  (with tool result)
OpenRouter AI Model
    ↓  "Here is your answer: PKR 95,000..."
ChatController
    ↓  {"reply": "You spent PKR 95,000 on Travel this month."}
Browser (chat.js)  ← renders bubble in sidebar
```

---

## Step-by-Step Technical Flow

### STEP 1 — User types a message in the browser

The user types in `#chat-input` and presses Enter or clicks Send.

`chat.js` runs:
```javascript
const res = await fetch('/chat/send', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ message: "How much did I spend on Travel this month?" })
});
```

The browser sends this **HTTP request** to your local CI4 server:

```
POST http://localhost:8080/chat/send
Content-Type: application/json

{
    "message": "How much did I spend on Travel this month?"
}
```

---

### STEP 2 — CI4 routes the request to ChatController::send()

`app/Config/Routes.php` has:
```php
$routes->post('chat/send', 'ChatController::send');
```

CI4 dispatches to `ChatController::send()`.

**What the controller does first:**

```php
// 1. Read the JSON body
$data = $this->request->getJSON(true);
// $data = ['message' => 'How much did I spend on Travel this month?']

// 2. Load conversation history from session (empty on first message)
$history = session()->get('chat_history') ?? [];
// $history = []

// 3. Append the user's message to history
$history[] = ['role' => 'user', 'content' => 'How much did I spend on Travel this month?'];
// $history = [
//   ['role' => 'user', 'content' => 'How much did I spend on Travel this month?']
// ]
```

---

### STEP 3 — Controller sends the first request to OpenRouter API

The controller calls `buildTools()` to define the 4 available database tools, then POSTs to OpenRouter.

**Exact request body sent to OpenRouter:**

```json
POST https://openrouter.ai/api/v1/chat/completions
Authorization: Bearer sk-or-v1-xxxxxxxxxxxx
Content-Type: application/json

{
  "model": "openrouter/free",
  "tool_choice": "auto",
  "messages": [
    {
      "role": "user",
      "content": "How much did I spend on Travel this month?"
    }
  ],
  "tools": [
    {
      "type": "function",
      "function": {
        "name": "get_expense_summary",
        "description": "Get the total amount spent and count of expenses. Use this when the user asks about total spending, how much they spent overall, in a specific period, by category, or by payment method.",
        "parameters": {
          "type": "object",
          "properties": {
            "date_from":       { "type": "string", "description": "Start date filter in YYYY-MM-DD format (optional)" },
            "date_to":         { "type": "string", "description": "End date filter in YYYY-MM-DD format (optional)" },
            "category":        { "type": "string", "description": "Filter by expense category e.g. Food, Transport, Utilities (optional)" },
            "payment_method":  { "type": "string", "enum": ["cash","card","bank_transfer","other"] }
          },
          "required": []
        }
      }
    },
    {
      "type": "function",
      "function": {
        "name": "list_expenses",
        "description": "List individual expense records...",
        "parameters": { ... }
      }
    },
    {
      "type": "function",
      "function": {
        "name": "get_category_breakdown",
        "description": "Get total amount spent grouped by category...",
        "parameters": { ... }
      }
    },
    {
      "type": "function",
      "function": {
        "name": "get_monthly_totals",
        "description": "Get total amount spent per month for a given calendar year...",
        "parameters": { ... }
      }
    }
  ]
}
```

**Key points:**
- `tool_choice: "auto"` means the AI freely decides whether to call a tool or answer directly
- The `messages` array is the full conversation history — the AI sees context from all prior turns
- The `tools` array tells the AI what database operations it is allowed to request

---

### STEP 4 — OpenRouter AI responds with a tool call request

The AI reads the question, understands it needs Travel data filtered to this month, and responds with `finish_reason: "tool_calls"` — meaning it is NOT giving a text answer yet, it is requesting a database query.

**Exact response from OpenRouter (Round 1):**

```json
HTTP 200 OK

{
  "id": "gen-abc123",
  "model": "openrouter/free",
  "choices": [
    {
      "finish_reason": "tool_calls",
      "message": {
        "role": "assistant",
        "content": null,
        "tool_calls": [
          {
            "id": "call_xyz789",
            "type": "function",
            "function": {
              "name": "get_expense_summary",
              "arguments": "{\"category\":\"Travel\",\"date_from\":\"2026-05-01\",\"date_to\":\"2026-05-31\"}"
            }
          }
        ]
      }
    }
  ],
  "usage": {
    "prompt_tokens": 312,
    "completion_tokens": 28,
    "total_tokens": 340
  }
}
```

**What happened here:**
- `finish_reason: "tool_calls"` → AI is NOT done, it wants data first
- `tool_calls[0].function.name` → `"get_expense_summary"` — AI picked the right tool
- `tool_calls[0].function.arguments` → `{"category":"Travel","date_from":"2026-05-01","date_to":"2026-05-31"}` — AI figured out the date range from "this month" by itself

---

### STEP 5 — Controller executes the tool against MySQL

The controller's `for` loop detects `finish_reason === "tool_calls"` and executes the tool:

```php
// Append the assistant's tool_call message to history
$history[] = $message; // the assistant message with tool_calls array

// Dispatch to the right PHP method
$args = json_decode('{"category":"Travel","date_from":"2026-05-01","date_to":"2026-05-31"}', true);

$result = match('get_expense_summary') {
    'get_expense_summary' => $this->toolGetExpenseSummary($args),
    ...
};
```

**Inside `toolGetExpenseSummary()`:**

```php
$model = new ExpenseModel();
$model->applyFilters([
    'category'  => 'Travel',
    'date_from' => '2026-05-01',
    'date_to'   => '2026-05-31',
]);
$model->selectSum('amount', 'total')->select('COUNT(*) as count');
$row = $model->get()->getRowArray();
```

**SQL that CI4 actually runs:**

```sql
SELECT SUM(amount) AS total, COUNT(*) AS count
FROM expense
WHERE category = 'Travel'
  AND expense_date >= '2026-05-01'
  AND expense_date <= '2026-05-31'
  AND deleted_at IS NULL
```

**MySQL returns:**

```
total    | count
---------|------
95000.00 | 2
```

**Tool result PHP returns:**

```php
['total' => 95000.0, 'count' => 2]
```

---

### STEP 6 — Controller appends the tool result to history and calls OpenRouter again

```php
$history[] = [
    'role'         => 'tool',
    'tool_call_id' => 'call_xyz789',   // must match the id from Step 4
    'content'      => '{"total":95000,"count":2}',
];
// loop continues → i = 1
```

**Second request body sent to OpenRouter:**

```json
POST https://openrouter.ai/api/v1/chat/completions
Authorization: Bearer sk-or-v1-xxxxxxxxxxxx
Content-Type: application/json

{
  "model": "openrouter/free",
  "tool_choice": "auto",
  "messages": [
    {
      "role": "user",
      "content": "How much did I spend on Travel this month?"
    },
    {
      "role": "assistant",
      "content": null,
      "tool_calls": [
        {
          "id": "call_xyz789",
          "type": "function",
          "function": {
            "name": "get_expense_summary",
            "arguments": "{\"category\":\"Travel\",\"date_from\":\"2026-05-01\",\"date_to\":\"2026-05-31\"}"
          }
        }
      ]
    },
    {
      "role": "tool",
      "tool_call_id": "call_xyz789",
      "content": "{\"total\":95000,\"count\":2}"
    }
  ],
  "tools": [ ... same 4 tools ... ]
}
```

**Notice:** the `messages` array now has 3 entries:
1. The user's original question
2. The assistant's tool call request (from Step 4)
3. The tool result from MySQL (from Step 5)

The AI now has the data it needs to write the final answer.

---

### STEP 7 — OpenRouter AI responds with the final text answer

**Exact response from OpenRouter (Round 2):**

```json
HTTP 200 OK

{
  "id": "gen-def456",
  "model": "openrouter/free",
  "choices": [
    {
      "finish_reason": "stop",
      "message": {
        "role": "assistant",
        "content": "You spent **PKR 95,000** on Travel this month (2 transactions). That includes expenses recorded between May 1 and May 31, 2026."
      }
    }
  ],
  "usage": {
    "prompt_tokens": 389,
    "completion_tokens": 41,
    "total_tokens": 430
  }
}
```

**What happened:**
- `finish_reason: "stop"` → AI is done, no more tool calls needed
- `message.content` → the final human-readable reply

---

### STEP 8 — Controller saves history and sends reply to browser

```php
$reply = "You spent **PKR 95,000** on Travel this month (2 transactions)...";

// Append final assistant reply to history
$history[] = ['role' => 'assistant', 'content' => $reply];

// Cap at 20 messages so session doesn't grow forever
if (count($history) > 20) {
    $history = array_slice($history, -20);
}

// Save updated history to CI4 session (file-backed)
session()->set('chat_history', $history);

// Return JSON to the browser
return $this->response->setJSON(['reply' => $reply]);
```

**HTTP response back to the browser:**

```json
HTTP 200 OK
Content-Type: application/json

{
  "reply": "You spent **PKR 95,000** on Travel this month (2 transactions). That includes expenses recorded between May 1 and May 31, 2026."
}
```

---

### STEP 9 — chat.js renders the reply in the sidebar

```javascript
const data = await res.json();
// data = { reply: "You spent **PKR 95,000** on Travel this month..." }

thinking.remove();               // removes the "Thinking…" bubble
appendMessage('ai', data.reply); // creates a new bubble, adds to #chat-messages
```

The user sees the answer appear in the sidebar as a grey bubble.

---

## Full Session History After This Exchange

This is what gets stored in `$_SESSION['chat_history']` after the conversation above:

```json
[
  {
    "role": "user",
    "content": "How much did I spend on Travel this month?"
  },
  {
    "role": "assistant",
    "content": null,
    "tool_calls": [
      {
        "id": "call_xyz789",
        "type": "function",
        "function": {
          "name": "get_expense_summary",
          "arguments": "{\"category\":\"Travel\",\"date_from\":\"2026-05-01\",\"date_to\":\"2026-05-31\"}"
        }
      }
    ]
  },
  {
    "role": "tool",
    "tool_call_id": "call_xyz789",
    "content": "{\"total\":95000,\"count\":2}"
  },
  {
    "role": "assistant",
    "content": "You spent **PKR 95,000** on Travel this month (2 transactions)..."
  }
]
```

On the **next message**, this entire array is included in the `messages` field sent to OpenRouter, so the AI remembers the previous question and answer.

---

## How the Tool Loop Works (the `for` loop in the controller)

```
Iteration 1:
  → Send messages to OpenRouter
  ← finish_reason = "tool_calls"   → execute tool(s), append results to $history
  → continue loop

Iteration 2:
  → Send messages (now including tool results) to OpenRouter
  ← finish_reason = "stop"         → extract $reply, break loop

Loop ends. Save history. Return reply.
```

Maximum 10 iterations (safety cap). For complex questions the AI may call 2–3 tools before answering. For a simple greeting it goes `stop` on iteration 1 with no tools called at all.

---

## Error Scenarios

| What goes wrong | HTTP status | What browser sees |
|---|---|---|
| API key missing in `.env` | 503 | "AI not configured" |
| OpenRouter rate limit (429) | 503 | "AI rate limit reached. Please wait…" |
| OpenRouter server error (5xx) | 502 | OpenRouter's error message |
| Network unreachable | 502 | "AI service unreachable: …" |
| Empty message sent | 400 | "Empty message" |

---

## The 4 Available Tools Summary

| Tool | MySQL query it runs | When AI uses it |
|---|---|---|
| `get_expense_summary` | `SELECT SUM(amount), COUNT(*) FROM expense WHERE ...` | "How much did I spend…" |
| `list_expenses` | `SELECT * FROM expense WHERE ... ORDER BY expense_date DESC LIMIT N` | "Show me my last N transactions" |
| `get_category_breakdown` | `SELECT category, SUM(amount) FROM expense GROUP BY category` | "Break down my spending…" |
| `get_monthly_totals` | `SELECT MONTH(expense_date), SUM(amount) FROM expense WHERE YEAR=... GROUP BY month` | "What did I spend each month…" |
