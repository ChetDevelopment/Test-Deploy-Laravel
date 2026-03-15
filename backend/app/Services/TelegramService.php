<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private ?string $botToken;
    private ?string $chatId;
    private bool $isConfigured;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
        $this->chatId = config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID'));
        $this->isConfigured = !empty($this->botToken) && !empty($this->chatId);
    }

    /**
     * Check if Telegram is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Send a message to Telegram
     */
    public function sendMessage(string $message, ?string $parseMode = 'HTML'): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Telegram is not configured. Please set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID in .env',
            ];
        }

        try {
            $response = Http::timeout(30)->post(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                [
                    'chat_id' => $this->chatId,
                    'text' => $message,
                    'parse_mode' => $parseMode,
                ]
            );

            $result = $response->json();

            if ($response->successful() && ($result['ok'] ?? false)) {
                Log::info('Telegram notification sent successfully', [
                    'message_id' => $result['result']['message_id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message_id' => $result['result']['message_id'] ?? null,
                ];
            }

            Log::error('Telegram API error', [
                'error' => $result['description'] ?? 'Unknown error',
                'response' => $result,
            ]);

            return [
                'success' => false,
                'error' => $result['description'] ?? 'Failed to send message',
            ];
        } catch (\Exception $e) {
            Log::error('Telegram notification failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send an absence alert for a student
     */
    public function sendAbsenceAlert(
        string $studentName,
        string $studentId,
        string $className,
        string $date,
        string $sessionTime,
        ?int $attendanceRecordId = null
    ): array {
        $message = $this->formatAbsenceMessage($studentName, $studentId, $className, $date, $sessionTime);
        
        // Add inline keyboard with Reason button if attendanceRecordId is provided
        if ($attendanceRecordId) {
            $webAppUrl = config('services.web_app.url', env('WEB_APP_URL', 'http://localhost:5173'));
            $reasonUrl = $webAppUrl . '/education/absence-reason?record=' . $attendanceRecordId;
            
            // Try with button - fallback to message without button if HTTPS not available
            $result = $this->sendMessageWithInlineKeyboard($message, $reasonUrl);
            
            // If button fails due to HTTPS requirement, send without button
            if (!$result['success'] && strpos($result['error'] ?? '', 'HTTPS') !== false) {
                Log::warning('Telegram inline keyboard requires HTTPS, sending without button');
                return $this->sendMessage($message);
            }
            
            return $result;
        }
        
        return $this->sendMessage($message);
    }

    /**
     * Send message with inline keyboard
     */
    public function sendMessageWithInlineKeyboard(string $message, string $reasonUrl): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Telegram is not configured. Please set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID in .env',
            ];
        }

        try {
            $response = Http::timeout(30)->post(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                [
                    'chat_id' => $this->chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => '📝 Add Reason / បន្ថែមហេតុផល',
                                    'url' => $reasonUrl
                                ]
                            ]
                        ]
                    ])
                ]
            );

            $result = $response->json();

            if ($response->successful() && ($result['ok'] ?? false)) {
                Log::info('Telegram notification with keyboard sent successfully', [
                    'message_id' => $result['result']['message_id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message_id' => $result['result']['message_id'] ?? null,
                ];
            }

            Log::error('Telegram API error', [
                'error' => $result['description'] ?? 'Unknown error',
                'response' => $result,
            ]);

            return [
                'success' => false,
                'error' => $result['description'] ?? 'Failed to send message',
            ];
        } catch (\Exception $e) {
            Log::error('Telegram notification failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format the absence alert message
     */
    private function formatAbsenceMessage(
        string $studentName,
        string $studentId,
        string $className,
        string $date,
        string $sessionTime
    ): string {
        $emoji = "⚠️";
        $title = "ABSENCE ALERT";
        
        return <<<MESSAGE
{$emoji} <b>{$title}</b> {$emoji}

👤 <b>Student:</b> {$studentName}
🆔 <b>ID:</b> {$studentId}
🏫 <b>Class:</b> {$className}
📅 <b>Date:</b> {$date}
⏰ <b>Session:</b> {$sessionTime}

<i>Please contact the student's parent/guardian.</i>
MESSAGE;
    }

    /**
     * Send a test message to verify connection
     */
    public function sendTestMessage(): array
    {
        $message = "✅ <b>Test Message</b>\n\nTelegram notification service is working correctly!";
        return $this->sendMessage($message);
    }

    /**
     * Get bot info (for testing connection)
     */
    public function getBotInfo(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Telegram is not configured',
            ];
        }

        try {
            $response = Http::timeout(30)->get(
                "https://api.telegram.org/bot{$this->botToken}/getMe"
            );

            $result = $response->json();

            if ($response->successful() && ($result['ok'] ?? false)) {
                return [
                    'success' => true,
                    'bot_info' => $result['result'],
                ];
            }

            return [
                'success' => false,
                'error' => $result['description'] ?? 'Failed to get bot info',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
