# Implementation Plan: AI-Powered Chat Sidebar
**Spec:** `.claude/specs/01-ai-powered-chat-sidebar.md`

---

## Context
Add a collapsible chat sidebar to every page of the Expense Tracker. The user types any free-form expense-related question. The PHP backend sends the question to the OpenRouter API (OpenAI-compatible, free tier) along with four function tool schemas. The model autonomously decides which tool(s) to call and with what arguments — the backend does not interpret the question itself. The backend executes the requested tools against MySQL, feeds results back, and loops until the model produces a final text reply. The reply is returned as JSON and rendered in the sidebar.

---

## Files to Create

| # | File | Purpose |
|---|---|---|
| 1 | `app/Config/Ai.php` | Reads `OPENROUTER_API_KEY` and `OPENROUTER_MODEL` from `.env` |
| 2 | `app/Controllers/ChatController.php` | `send()` + `clear()` — API call, tool dispatch loop, session history |
| 3 | `app/Views/chat/_sidebar.php` | Sidebar HTML — message list, input form |
| 4 | `public/assets/css/chat.css` | Sidebar layout, slide animation, message bubbles |
| 5 | `public/assets/js/chat.js` | Toggle, fetch, render reply, keyboard shortcuts |

## Files to Modify

| File | What changes |
|---|---|
| `app/Models/ExpenseModel.php` | Add `getGroupedByCategory()` and `getMonthlyTotals()` |
| `app/Config/Routes.php` | Add `POST chat/send` and `DELETE chat/clear` |
| `app/Views/layouts/main.php` | Include sidebar partial, chat CSS + JS |

---

## Implementation Steps

### Step 1 — `app/Config/Ai.php`

Plain CI4 config class. Reads credentials from `.env` via `env()` — never hard-coded.

```php
declare(strict_types=1);
namespace App\Config;
use CodeIgniter\Config\BaseConfig;

class Ai extends BaseConfig
{
    public string $apiKey  = '';
    public string $model   = '';
    public string $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = env('OPENROUTER_API_KEY', '');
        $this->model  = env('OPENROUTER_MODEL', 'meta-llama/llama-3.3-70b-instruct:free');
    }
}
```

---

### Step 2 — `app/Models/ExpenseModel.php` (modify)

Add two methods below the existing ones. Do not touch `$allowedFields`.

**`getGroupedByCategory(array $filters = []): array`**
- `$this->db->table('expense')`
- `->select('category')->selectSum('amount', 'total')`
- `->where('deleted_at IS NULL')`
- Apply `category`, `payment_method`, `date_from`, `date_to` filters the same way as the existing `applyFilters()` pattern
- `->groupBy('category')->orderBy('total', 'DESC')->get()->getResultArray()`

**`getMonthlyTotals(int $year): array`**
- `$this->db->table('expense')`
- `->select('MONTH(expense_date) as month')->selectSum('amount', 'total')`
- `->where('YEAR(expense_date)', $year)->where('deleted_at IS NULL')`
- `->groupBy('month')->orderBy('month', 'ASC')->get()->getResultArray()`

---

### Step 3 — `app/Controllers/ChatController.php` (create)

Namespace: `App\Controllers`. Extends `BaseController`.

#### `send()` — full flow

```
1. Read JSON body: $data = $this->request->getJSON(true)
2. Guard: if empty($data['message']) → return setJSON(['error'=>'Empty message']) HTTP 400
3. Guard: if env('OPENROUTER_API_KEY') is blank → return setJSON(['error'=>'AI not configured']) HTTP 503
4. Load $history = session()->get('chat_history') ?? []
5. Append ['role'=>'user', 'content'=>trim($data['message'])] to $history
6. Define $tools array (4 function schemas — see below)
7. Tool loop:
     POST to $aiConfig->baseUrl via Services::curlrequest()
     Headers: Authorization: Bearer {apiKey}, Content-Type: application/json, HTTP-Referer: http://localhost
     Body: json_encode(['model'=>$model, 'messages'=>$history, 'tools'=>$tools, 'tool_choice'=>'auto'])
     Decode response JSON
     $choice = $response['choices'][0]
     if $choice['finish_reason'] === 'tool_calls':
         Append assistant message (with tool_calls array) to $history
         foreach $choice['message']['tool_calls'] as $call:
             $args = json_decode($call['function']['arguments'], true)
             $result = match($call['function']['name']) {
                 'get_expense_summary'    => $this->toolGetExpenseSummary($args),
                 'list_expenses'          => $this->toolListExpenses($args),
                 'get_category_breakdown' => $this->toolGetCategoryBreakdown($args),
                 'get_monthly_totals'     => $this->toolGetMonthlyTotals($args),
             }
             Append ['role'=>'tool','tool_call_id'=>$call['id'],'content'=>json_encode($result)]
         continue loop
     if $choice['finish_reason'] === 'stop':
         $reply = $choice['message']['content']
         break
8. Append ['role'=>'assistant','content'=>$reply] to $history
9. Cap $history to last 20 entries
10. session()->set('chat_history', $history)
11. return $this->response->setJSON(['reply' => $reply])
```

#### Tool schemas (`$tools` array)

Each entry must have a detailed `description` so the model can map any natural-language expense question to the right tool without any pre-processing in PHP.

```
get_expense_summary
  description: "Get the total amount spent and count of expenses. Use this when the user asks
                about total spending, how much they spent overall or in a specific period,
                category, or payment method."
  parameters: date_from (string, YYYY-MM-DD, optional), date_to (string, optional),
              category (string, optional), payment_method (string: cash|card|bank_transfer|other, optional)

list_expenses
  description: "List individual expense records. Use this when the user wants to see specific
                transactions, recent purchases, or a history of expenses."
  parameters: limit (integer 1-20, required), category (string, optional),
              date_from (string, optional), date_to (string, optional)

get_category_breakdown
  description: "Get total amount spent grouped by category. Use this when the user asks about
                spending breakdown, which categories cost the most, or wants a category summary."
  parameters: date_from (string, optional), date_to (string, optional)

get_monthly_totals
  description: "Get total amount spent per month for a given calendar year. Use this when the
                user asks about monthly spending patterns, trends, or year overview."
  parameters: year (integer, required — use the current year if not specified)
```

#### Private tool methods (called from the match expression)

```
toolGetExpenseSummary(array $args): array
  → new ExpenseModel, applyFilters($args), selectSum/count, first()
  → return ['total' => float, 'count' => int]

toolListExpenses(array $args): array
  → new ExpenseModel, applyFilters($args), orderBy('expense_date','DESC'), findAll($limit)
  → return array of expense rows (title, amount, category, expense_date, payment_method)

toolGetCategoryBreakdown(array $args): array
  → $expenseModel->getGroupedByCategory($args)
  → return array of ['category'=>..., 'total'=>...]

toolGetMonthlyTotals(array $args): array
  → $expenseModel->getMonthlyTotals($args['year'] ?? (int)date('Y'))
  → return array of ['month'=>int, 'total'=>float]
```

#### `clear()` method

```php
session()->remove('chat_history');
return $this->response->setJSON(['ok' => true]);
```

---

### Step 4 — `app/Config/Routes.php` (modify)

Add below the existing `get('/')` line:

```php
$routes->post('chat/send',    'ChatController::send');
$routes->delete('chat/clear', 'ChatController::clear');
```

---

### Step 5 — `app/Views/chat/_sidebar.php` (create)

```
div#chat-sidebar   (fixed, right, full height, hidden off-screen by default)
├── div.chat-header
│   ├── span  "SpendWise AI"  (gold icon + text)
│   └── div.d-flex.gap-2
│       ├── button#chat-clear-btn  "Clear"  (small, outline)
│       └── button#chat-close-btn  "×"
├── div#chat-messages  (flex-column, overflow-y auto, flex-grow-1)
│   └── (bubbles rendered here by JS)
└── div.chat-footer
    └── form#chat-form.d-flex.gap-2
        ├── input#chat-input  (type=text, placeholder="Ask anything about your expenses…")
        └── button  Send  (btn-gold)
```

- User bubbles: align-self-end, navy bg, white text
- Assistant bubbles: align-self-start, white bg, dark text, light border
- A "Thinking…" animated bubble is inserted by JS while waiting for the response and removed when reply arrives

---

### Step 6 — `public/assets/css/chat.css` (create)

Key rules:

```css
#chat-sidebar {
  position: fixed; top: 0; right: 0;
  width: 380px; height: 100vh;
  transform: translateX(100%);
  transition: transform 0.3s ease;
  z-index: 1050;
  background: #fff;
  display: flex; flex-direction: column;
  box-shadow: -4px 0 20px rgba(0,0,0,.15);
}
#chat-sidebar.open { transform: translateX(0); }

#chat-toggle-btn {
  position: fixed; bottom: 24px; right: 24px;
  z-index: 1060;
  background: var(--navy); color: var(--gold);
  border: none; border-radius: 50%;
  width: 52px; height: 52px; font-size: 1.4rem;
}

.chat-bubble { max-width: 80%; border-radius: 16px; padding: .6rem 1rem; margin-bottom: .5rem; }
.chat-bubble-user { background: var(--navy); color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
.chat-bubble-ai   { background: #f1f5f9; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 4px; }
.chat-bubble-thinking { opacity: .6; font-style: italic; }
```

---

### Step 7 — `public/assets/js/chat.js` (create)

Functions:

```
toggleSidebar()       — add/remove .open on #chat-sidebar; toggle aria-expanded on toggle btn
closeSidebar()        — remove .open
appendMessage(role, text) — create .chat-bubble div, append to #chat-messages, scroll to bottom
showThinking()        — appendMessage with class chat-bubble-thinking, return the element
sendMessage()         — read #chat-input value, guard empty,
                         appendMessage('user', text), clear input,
                         thinkingEl = showThinking(),
                         fetch POST /chat/send with JSON body,
                         remove thinkingEl, appendMessage('ai', data.reply)
clearHistory()        — fetch DELETE /chat/clear, clear #chat-messages DOM

Event listeners:
  #chat-toggle-btn  click  → toggleSidebar()
  #chat-close-btn   click  → closeSidebar()
  #chat-clear-btn   click  → clearHistory()
  #chat-form        submit → e.preventDefault(); sendMessage()
  document          keydown → if key==='Escape' closeSidebar()
```

No external libraries. Plain `fetch`. No module syntax (keep compatible with plain `<script>`).

---

### Step 8 — `app/Views/layouts/main.php` (modify)

In `<head>` — after Bootstrap CSS `<link>`:
```html
<link rel="stylesheet" href="<?= base_url('assets/css/chat.css') ?>">
```

Before `</body>` — after Bootstrap JS `<script>`:
```html
<?= view('chat/_sidebar') ?>
<button id="chat-toggle-btn" aria-label="Open AI chat" aria-expanded="false">
    <i class="bi bi-chat-dots-fill"></i>
</button>
<script src="<?= base_url('assets/js/chat.js') ?>"></script>
```

---

## Verification

Run `php spark serve` then verify each item:

| # | Test | Expected |
|---|---|---|
| 1 | `POST /chat/send` body `{"message":"hello"}` | HTTP 200, `{"reply":"..."}` |
| 2 | `POST /chat/send` with `OPENROUTER_API_KEY` removed from `.env` | HTTP 503, `{"error":"AI not configured"}` |
| 3 | `DELETE /chat/clear` | HTTP 200, `{"ok":true}` |
| 4 | Click toggle button | Sidebar slides in from right |
| 5 | Press Escape | Sidebar closes |
| 6 | Ask "How much did I spend in total?" | PKR total returned |
| 7 | Ask "How much did I spend on Food?" | Filtered total for Food category |
| 8 | Ask "Show me my last 3 transactions" | List of 3 expense rows |
| 9 | Ask "Break down my spending by category" | Per-category amounts |
| 10 | Ask "What did I spend each month this year?" | Monthly totals for current year |
| 11 | Ask a compound question e.g. "Which category costs most and list recent ones from it?" | Model calls 2+ tools in one turn |
| 12 | Ask two follow-up questions | Second answer is context-aware |
| 13 | Click Clear, ask again | Fresh conversation, no prior context |
