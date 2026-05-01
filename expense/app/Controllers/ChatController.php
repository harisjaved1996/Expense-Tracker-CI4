<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Ai;
use App\Models\ExpenseModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Exceptions\HTTPException;

class ChatController extends BaseController
{
    public function send(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (empty($data['message']) || !is_string($data['message'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Empty message']);
        }

        $ai = new Ai();

        if ($ai->apiKey === '') {
            return $this->response->setStatusCode(503)->setJSON(['error' => 'AI not configured']);
        }

        $history = session()->get('chat_history') ?? [];
        $history[] = ['role' => 'user', 'content' => trim($data['message'])];

        $tools = $this->buildTools();

        $reply = '';
        $maxIterations = 10;

        for ($i = 0; $i < $maxIterations; $i++) {
            $client = \Config\Services::curlrequest();

            try {
                $response = $client->post($ai->baseUrl, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $ai->apiKey,
                        'Content-Type'  => 'application/json',
                        'HTTP-Referer'  => 'http://localhost',
                    ],
                    'http_errors' => false,
                    'body'        => json_encode([
                        'model'       => $ai->model,
                        'messages'    => $history,
                        'tools'       => $tools,
                        'tool_choice' => 'auto',
                    ]),
                ]);
            } catch (HTTPException $e) {
                return $this->response->setStatusCode(502)->setJSON(['error' => 'AI service unreachable: ' . $e->getMessage()]);
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode === 429) {
                return $this->response->setStatusCode(503)->setJSON(['error' => 'AI rate limit reached. Please wait a moment and try again.']);
            }

            if ($statusCode >= 400) {
                $body = json_decode($response->getBody(), true);
                $msg  = $body['error']['message'] ?? ('AI service error (HTTP ' . $statusCode . ')');
                return $this->response->setStatusCode(502)->setJSON(['error' => $msg]);
            }

            $decoded = json_decode($response->getBody(), true);
            $choice  = $decoded['choices'][0] ?? null;

            if ($choice === null) {
                break;
            }

            $finishReason = $choice['finish_reason'] ?? 'stop';
            $message      = $choice['message'];

            if ($finishReason === 'tool_calls') {
                $history[] = $message;

                foreach ($message['tool_calls'] as $call) {
                    $args   = json_decode($call['function']['arguments'], true) ?? [];
                    $name   = $call['function']['name'];
                    $result = match ($name) {
                        'get_expense_summary'    => $this->toolGetExpenseSummary($args),
                        'list_expenses'          => $this->toolListExpenses($args),
                        'get_category_breakdown' => $this->toolGetCategoryBreakdown($args),
                        'get_monthly_totals'     => $this->toolGetMonthlyTotals($args),
                        default                  => ['error' => 'Unknown tool: ' . $name],
                    };

                    $history[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $call['id'],
                        'content'      => json_encode($result),
                    ];
                }

                continue;
            }

            $reply = $message['content'] ?? '';
            break;
        }

        $history[] = ['role' => 'assistant', 'content' => $reply];

        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        session()->set('chat_history', $history);

        return $this->response->setJSON(['reply' => $reply]);
    }

    public function clear(): ResponseInterface
    {
        session()->remove('chat_history');

        return $this->response->setJSON(['ok' => true]);
    }

    private function buildTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'get_expense_summary',
                    'description' => 'Get the total amount spent and count of expenses. Use this when the user asks about total spending, how much they spent overall, in a specific period, by category, or by payment method.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'date_from' => [
                                'type'        => 'string',
                                'description' => 'Start date filter in YYYY-MM-DD format (optional)',
                            ],
                            'date_to' => [
                                'type'        => 'string',
                                'description' => 'End date filter in YYYY-MM-DD format (optional)',
                            ],
                            'category' => [
                                'type'        => 'string',
                                'description' => 'Filter by expense category e.g. Food, Transport, Utilities (optional)',
                            ],
                            'payment_method' => [
                                'type'        => 'string',
                                'enum'        => ['cash', 'card', 'bank_transfer', 'other'],
                                'description' => 'Filter by payment method (optional)',
                            ],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'list_expenses',
                    'description' => 'List individual expense records. Use this when the user wants to see specific transactions, recent purchases, or a history of expenses.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'limit' => [
                                'type'        => 'integer',
                                'description' => 'Number of expenses to return (1–20)',
                                'minimum'     => 1,
                                'maximum'     => 20,
                            ],
                            'category' => [
                                'type'        => 'string',
                                'description' => 'Filter by category (optional)',
                            ],
                            'date_from' => [
                                'type'        => 'string',
                                'description' => 'Start date in YYYY-MM-DD format (optional)',
                            ],
                            'date_to' => [
                                'type'        => 'string',
                                'description' => 'End date in YYYY-MM-DD format (optional)',
                            ],
                        ],
                        'required' => ['limit'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'get_category_breakdown',
                    'description' => 'Get total amount spent grouped by category. Use this when the user asks about spending breakdown, which categories cost the most, or wants a category-by-category summary.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'date_from' => [
                                'type'        => 'string',
                                'description' => 'Start date in YYYY-MM-DD format (optional)',
                            ],
                            'date_to' => [
                                'type'        => 'string',
                                'description' => 'End date in YYYY-MM-DD format (optional)',
                            ],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name'        => 'get_monthly_totals',
                    'description' => 'Get total amount spent per month for a given calendar year. Use this when the user asks about monthly spending patterns, trends over the year, or wants a month-by-month overview.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'year' => [
                                'type'        => 'integer',
                                'description' => 'The calendar year to get monthly totals for. Use the current year if not specified.',
                            ],
                        ],
                        'required' => ['year'],
                    ],
                ],
            ],
        ];
    }

    private function toolGetExpenseSummary(array $args): array
    {
        $model = new ExpenseModel();
        $model->applyFilters($args);
        $model->selectSum('amount', 'total')->select('COUNT(*) as count');

        $row = $model->get()->getRowArray();

        return [
            'total' => (float) ($row['total'] ?? 0),
            'count' => (int) ($row['count'] ?? 0),
        ];
    }

    private function toolListExpenses(array $args): array
    {
        $limit = max(1, min(20, (int) ($args['limit'] ?? 5)));
        $model = new ExpenseModel();
        $model->applyFilters($args);

        return $model->orderBy('expense_date', 'DESC')->findAll($limit);
    }

    private function toolGetCategoryBreakdown(array $args): array
    {
        $model = new ExpenseModel();

        return $model->getGroupedByCategory($args);
    }

    private function toolGetMonthlyTotals(array $args): array
    {
        $year  = (int) ($args['year'] ?? date('Y'));
        $model = new ExpenseModel();

        return $model->getMonthlyTotals($year);
    }
}
