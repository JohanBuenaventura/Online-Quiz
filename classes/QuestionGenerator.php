<?php

class QuestionGenerator {

    // =========================
    // 1. SIMPLE HEURISTIC GENERATOR (unchanged)
    // =========================
    public static function generateHeuristic($text, $count = 5, $choices = 4) {
        $sentences = self::splitSentences($text);
        $candidates = [];
        foreach ($sentences as $s) {
            $words = preg_split('/\W+/', $s, -1, PREG_SPLIT_NO_EMPTY);
            if (count($words) < 6) continue;
            foreach ($words as $w) {
                if (strlen($w) > 4 && !is_numeric($w)) {
                    $clean = trim($w, ",.\n\r\t'");
                    if (strlen($clean) > 3) $candidates[] = $clean;
                }
            }
        }
        $candidates = array_values(array_unique($candidates));
        shuffle($candidates);

        $questions = [];
        $i = 0;

        foreach ($sentences as $s) {
            if ($i >= $count) break;
            foreach ($candidates as $cand) {
                if (stripos($s, $cand) !== false) {
                    $answer = $cand;
                    $question_text = str_ireplace($answer, '_____', $s);
                    $distractors = array_filter($candidates, fn($x) => strtolower($x) !== strtolower($answer));
                    shuffle($distractors);
                    $opts = array_slice($distractors, 0, max(0, $choices - 1));
                    $opts[] = $answer;
                    shuffle($opts);
                    $correct_index = array_search($answer, $opts);
                    $questions[] = [
                        'question' => trim($question_text),
                        'choices' => $opts,
                        'correct_index' => $correct_index === false ? 0 : $correct_index,
                    ];
                    $i++;
                    break;
                }
            }
        }

        // fallback if not enough
        if (count($questions) < $count) {
            $needed = $count - count($questions);
            for ($k = 0; $k < $needed && $k < count($candidates); $k++) {
                $answer = $candidates[$k];
                $question_text = "What is the correct word for: {$answer}?";
                $distractors = array_filter($candidates, fn($x) => strtolower($x) !== strtolower($answer));
                shuffle($distractors);
                $opts = array_slice($distractors, 0, max(0, $choices - 1));
                $opts[] = $answer;
                shuffle($opts);
                $questions[] = [
                    'question' => $question_text,
                    'choices' => $opts,
                    'correct_index' => array_search($answer, $opts)
                ];
            }
        }

        return $questions;
    }

    // =========================
    // 2. NEW GEMINI AI GENERATOR
    // =========================
    public static function generateWithGemini($text, $count = 5, $choices = 4, $apiKey = null, $model = 'models/text-bison-001') {

        if (!$apiKey) {
            return ['error' => 'No Gemini API key provided'];
        }

        $snippet = trim(preg_replace('/\s+/', ' ', substr($text, 0, 4000)));

        $prompt = "
You are an AI that generates exam questions.

Create EXACTLY $count multiple-choice questions (MCQs) from the following text.

Each question must include:
- \"question\": string
- \"choices\": an array with exactly $choices options
- \"correct_index\": the correct answer index (0-based)

Return ONLY JSON, like this:

[
  {
    \"question\": \"...\",
    \"choices\": [\"A\", \"B\", \"C\", \"D\"],
    \"correct_index\": 1
  }
]

TEXT:
$snippet
";

        // Gemini API endpoint
        $url = "https://generativelanguage.googleapis.com/v1/models/$model:generateContent?key=$apiKey";

        // Payload
        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        // cURL request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0;
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: '';

        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return ["error" => "Curl error: " . $err];
        }

        curl_close($ch);

        // Try to parse top-level JSON
        $data = json_decode($response, true);

        // If top-level JSON is present and contains an error payload, return useful message
        if (is_array($data) && isset($data['error'])) {
            return ["error" => "Gemini API error: " . (is_string($data['error']) ? $data['error'] : json_encode($data['error']))];
        }

        // Helper to recursively find first candidate text / string that looks like the AI output
        $findJsonString = function ($node) use (&$findJsonString) {
            if (is_string($node)) {
                // if looks like JSON array/object, return it
                $trim = trim($node);
                if ((strpos($trim, '[') === 0) || (strpos($trim, '{') === 0)) return $node;
                // also return if long text (fallback)
                if (strlen($trim) > 20) return $node;
                return null;
            }
            if (is_array($node)) {
                foreach ($node as $k => $v) {
                    $res = $findJsonString($v);
                    if ($res !== null) return $res;
                }
            }
            return null;
        };

        $jsonText = null;

        // Common Gemini response shape
        if (is_array($data) && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $jsonText = $data['candidates'][0]['content']['parts'][0]['text'];
        } elseif (is_array($data)) {
            // try to find any string in response that could contain the JSON we expect
            $jsonText = $findJsonString($data);
        } else {
            // response not JSON; treat raw response as candidate
            $jsonText = $response;
        }

        if ($jsonText === null) {
            return ["error" => "Invalid response from Gemini (no usable content found)"];
        }

        // If jsonText appears to be wrapped HTML or contains a JSON blob, try to extract JSON array/object
        $decoded = self::extractJsonArrayOrObject($jsonText);
        if ($decoded !== null) {
            return $decoded;
        }

        // Try direct JSON decode (maybe the model returned pure JSON)
        $try = json_decode($jsonText, true);
        if (is_array($try)) return $try;

        // Last resort: generic decode failure
        return ["error" => "Failed to decode Gemini JSON output"];
    }

    // =========================
    // 3. OLD OPENAI FUNCTION REMOVED / UNUSED
    // =========================

    private static function extractJsonArrayOrObject($s) {
        // (kept for fallback / compatibility)
        $s = trim($s);
        $d = json_decode($s, true);
        if (is_array($d)) {
            if (isset($d['questions']) && is_array($d['questions'])) return $d['questions'];
            return $d;
        }
        $start = strpos($s, '[');
        $end = strrpos($s, ']');
        if ($start !== false && $end !== false && $end > $start) {
            $substr = substr($s, $start, $end - $start + 1);
            $decoded = json_decode($substr, true);
            if (is_array($decoded)) return $decoded;
        }
        return null;
    }

    private static function splitSentences($text) {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        $sentences = preg_split('/(?<=[.?!])\s+(?=[A-Z0-9])/', $text);
        return $sentences ?: [];
    }
}

