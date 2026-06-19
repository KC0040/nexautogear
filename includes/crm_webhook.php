<?php
// Agentic CRM Webhook — 填入你的 webhook URL
define('CRM_WEBHOOK_URL', getenv('CRM_WEBHOOK_URL') ?: '');

/**
 * 發送資料到 CRM Webhook
 * $event: 'b2b_application' | 'new_order' | 'inquiry'
 * $data: 關聯陣列
 */
function crm_push(string $event, array $data): void {
    $url = CRM_WEBHOOK_URL;
    if (empty($url)) return;

    $payload = json_encode([
        'event'     => $event,
        'source'    => 'nexautogear',
        'timestamp' => date('c'),
        'data'      => $data,
    ]);

    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/json\r\nUser-Agent: NEXAutogear/1.0\r\n",
        'content'       => $payload,
        'timeout'       => 5,
        'ignore_errors' => true,
    ]]);

    @file_get_contents($url, false, $ctx);
}
