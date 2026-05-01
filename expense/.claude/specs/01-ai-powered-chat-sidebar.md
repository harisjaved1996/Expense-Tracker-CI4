---
# Spec: AI-Powered Chat Sidebar with Internal Tool Calling

## Overview
A collapsible chat sidebar embedded in the main layout that lets the user ask natural-language questions about their expense data. The user types a message (e.g. "How much did I spend on Food this month?"), the PHP backend calls the OpenRouter API (OpenAI-compatible) with a defined set of function tools, the AI decides which tool(s) to call, the backend executes those tools against the MySQL database, and the final answer is returned as JSON and rendered in the sidebar. This is the first AI feature in the Expense Tracker and establishes the pattern for all future AI interactions.

## Depends on
- The `expense` table and `ExpenseModel` must exist (migration `2026-04-29-000001_CreateExpenseTable` already applied).

## Routes
- `POST /chat/send` → `ChatController::send` — accepts `{message: string}` JSON body, returns `{reply: string}` JSON — anonymous (no auth yet)
- `DELETE /chat/clear` → `ChatController::clear` — clears session-stored conversation history — anonymous

## Database changes
No database changes. Conversation history is stored in the CI4 session (file-backed by default) to avoid a migration. If the session expires, history resets — acceptable for this stage.

## Models
- **New models:** none
- **Modify:** `app/Models/ExpenseModel.php`
  - Add `getGroupedByCategory(array $filters): array` — returns total amount per category
  - Add `getMonthlyTotals(int $year): array` — returns total amount per month for a given year
  - No changes to `$allowedFields`

## Views
- **Create:** `app/Views/chat/_sidebar.php` — the full sidebar HTML panel (messages list + input form)
- **Modify:** `app/Views/layouts/main.php`
  - Add a floating chat toggle button (bottom-right, fixed position) in `<body>`
  - Include `_sidebar.php` partial just before `</body>`
  - Add `<link>` to `public/assets/css/chat.css` in `<head>`
  - Add `<script>` for `public/assets/js/chat.js` before `</body>`

## Files to change
| File | What changes |
|---|---|
| `app/Views/layouts/main.php` | Add sidebar partial, toggle button, chat CSS/JS references |
| `app/Models/ExpenseModel.php` | Add `getGroupedByCategory()` and `getMonthlyTotals()` |
| `app/Config/Routes.php` | Register `POST /chat/send` and `DELETE /chat/clear` |

## Files to create
| File | Purpose |
|---|---|
| `app/Controllers/ChatController.php` | Handles chat messages, calls Anthropic API, executes tools |
| `app/Config/Ai.php` | Holds `apiKey`, `model`, `baseUrl` loaded from `.env` via `env()` |
| `app/Views/chat/_sidebar.php` | Chat sidebar HTML |
| `public/assets/css/chat.css` | Sidebar, bubble, message styles |
| `public/assets/js/chat.js` | Toggle sidebar, send message via fetch, render reply |

## New dependencies
No new Composer packages. Use CI4's built-in `\Config\Services::curlrequest()` to call the OpenRouter API over HTTPS.

Add to `.env` (already added):
```
OPENROUTER_API_KEY = sk-or-v1-...
OPENROUTER_MODEL   = meta-llama/llama-3.3-70b-instruct:free
```

OpenRouter base URL: `https://openrouter.ai/api/v1/chat/completions`
Auth header: `Authorization: Bearer {OPENROUTER_API_KEY}`
Request/response format: OpenAI chat completions (not Anthropic messages API)

## Internal tools exposed to the model
The user can ask anything expense-related in natural language — the model autonomously decides which tool(s) to call, in what order, and with what arguments. The controller does not pre-interpret the question; it only executes whatever the model requests and feeds the results back.

The controller defines these as OpenAI-style function schemas (`tools` array with `type: "function"`) and maps each `tool_calls` entry to a PHP method:

| Tool name | What it queries | Parameters |
|---|---|---|
| `get_expense_summary` | Total amount + count from the `expense` table, with optional filters | `date_from?`, `date_to?`, `category?`, `payment_method?` |
| `list_expenses` | Most recent N individual expense rows | `limit` (1–20), `category?`, `date_from?`, `date_to?` |
| `get_category_breakdown` | Sum of amount grouped by category | `date_from?`, `date_to?` |
| `get_monthly_totals` | Sum of amount grouped by month for a given year | `year` |

Each tool description in the schema must be detailed enough for the model to infer when to use it from a free-form user question (e.g. "What did I waste most money on?" → `get_category_breakdown`; "Show me my last 5 transactions" → `list_expenses` with `limit=5`).

Tool execution happens inside the controller; results are sent back as `role: "tool"` messages with the matching `tool_call_id`. The loop repeats until the model returns a `finish_reason: "stop"` with no further tool calls — the model may call multiple tools in one turn if the question requires it.

## Rules for implementation
- Follow CodeIgniter 4 MVC conventions — controllers in `app/Controllers/`, models in `app/Models/`
- Declare `declare(strict_types=1);` at the top of every PHP file
- Use CI4 Query Builder via the Model API (`$model->save()`, `$model->find()`, etc.) — no raw SQL in application code
- Validate at the controller boundary using CI4's Validation library; trust CI4 internals below
- Keep controllers thin — move query logic into the Model if it exceeds ~20 lines
- Namespaces must match folder path: controllers → `App\Controllers`, models → `App\Models`
- Migrations use `$this->forge` only — never edit a migration after it has been applied
- Bootstrap 5 utility classes preferred; custom CSS only when Bootstrap cannot do it
- All custom styles go in `public/assets/css/`; all custom scripts in `public/assets/js/`
- All views must extend `app/Views/layouts/main.php`
- No npm, Webpack, TypeScript, or JS build tooling
- No comments describing what the code does — only when the why is non-obvious
- The OpenRouter API call must use `Services::curlrequest()` — no raw curl_* functions, no Guzzle
- Request body is JSON: `{model, messages, tools, tool_choice: "auto"}`; auth via `Authorization: Bearer` header — `tool_choice: "auto"` is mandatory so the model freely decides which tools to call
- Tool descriptions in the schema must be written in plain English precise enough for the model to infer the right tool from any natural-language expense question
- Store conversation history in `session()->get('chat_history')` as an array of OpenAI-format message objects `{role, content}`; cap history at 20 messages to stay within token limits
- The `ChatController::send()` method must return JSON (`$this->response->setJSON(...)`) — never a view
- Tool dispatch must be a `switch` or match expression — no dynamic method calls via variable strings
- The `app/Config/Ai.php` config class must read from `.env` via `env()` helper — never hard-code API keys
- The chat toggle button must be positioned `fixed` so it overlays all pages without affecting layout flow
- The sidebar must be keyboard-accessible: `Escape` closes it, `Enter` in the input submits the message

## Definition of done

### API endpoint
- [ ] `POST /chat/send` with any expense-related free-text message returns `{"reply":"..."}` with HTTP 200
- [ ] If `OPENROUTER_API_KEY` is missing or blank, returns `{"error":"AI not configured"}` HTTP 503 — no PHP exception leaks to the browser
- [ ] `DELETE /chat/clear` resets session history; subsequent messages start a fresh conversation

### Tool calling — the model must autonomously choose the right tool(s) for any query
- [ ] A question about totals (e.g. "How much did I spend overall?") triggers `get_expense_summary` and returns a PKR figure
- [ ] A question about a specific category (e.g. "How much on groceries?") triggers `get_expense_summary` with the correct `category` argument
- [ ] A question asking to list recent purchases triggers `list_expenses` with an appropriate `limit`
- [ ] A question about spending breakdown triggers `get_category_breakdown` and returns per-category amounts
- [ ] A question about monthly patterns triggers `get_monthly_totals` for the correct year
- [ ] A compound question (e.g. "Which category costs most and show me recent ones from it?") results in the model calling multiple tools in one turn

### UI / UX
- [ ] Chat sidebar opens and closes when the floating toggle button is clicked
- [ ] User can type any question, press Enter or click Send, and see the assistant reply in the sidebar
- [ ] Pressing Escape closes the sidebar
- [ ] Conversation history persists across multiple messages in the same session — the model remembers prior turns
- [ ] No JS build step — the feature works with a plain `php spark serve` and no additional tooling
