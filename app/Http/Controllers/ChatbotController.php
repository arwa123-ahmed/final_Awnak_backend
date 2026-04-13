<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ChatbotResponse;

class ChatbotController extends Controller
{
    public function reply(Request $request)
    {
        $message = strtolower($request->message);

        $responses = ChatbotResponse::all();

        $bestMatch = null;
        $highestScore = 0;

        foreach ($responses as $res) {

            // 1. compare question
            similar_text($message, strtolower($res->question), $percent);

            // 2. compare keywords
            $keywords = explode(',', $res->keywords);
            foreach ($keywords as $word) {
                similar_text($message, trim($word), $keywordPercent);

                if ($keywordPercent > $percent) {
                    $percent = $keywordPercent;
                }
            }

            // best match
            if ($percent > $highestScore) {
                $highestScore = $percent;
                $bestMatch = $res;
            }
        }

        if ($bestMatch && $highestScore > 40) {
            return response()->json([
                'reply' => $bestMatch->answer,
                'score' => $highestScore
            ]);
        }

        return response()->json([
            'reply' => 'مش فاهم سؤالك 😅 ممكن توضح أكتر؟'
        ]);
    }
}
