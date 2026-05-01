<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ChatbotResponse;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
   public function reply(Request $request)
{
    $request->validate([
        'message' => 'required|string'
    ]);

    try {
        $user = $request->user();

        // نجيب تاريخ المحادثة تبع اليوزر
        $history = ChatbotResponse::where('user_id', $user->id)
                          ->latest()
                          ->take(10)
                          ->get()
                          ->reverse();

        // نبني المحادثة للـ AI
        $messages = [
            [
                'role' => 'system',
                'content' => 'أنت مساعد ذكي لموقع بنك الوقت، منصة لتبادل الخدمات بين الأشخاص بالوقت بدل المال.
                ## لماذا بنك الوقت؟
                - كثير من الناس عندهم وقت ومهارات لكن ما في نظام يستغلوها
                - كثير من الناس محتاجين مساعدة لكن ما عندهم مال
                - الحل: تبادل الخدمات بالوقت بشكل عادل وشفاف
               ## خطوات التسجيل:
                1. سجل حساب جديد
                2. ارفع صورة الهوية (رقم قومي أو باسبور)
                3. انتظر موافقة الأدمن لتفعيل حسابك
                4. بعد التفعيل تقدر تستخدم الموقع كامل
              ## الدور:
                - كل مستخدم يختار دوره: Customer أو Volunteer وقابل للتغيير
                - Customer: ينزل خدمة محتاجها وتظهر للمتطوعين
                - Volunteer: يشوف الخدمات المطلوبة ويرد عليها
              ## الرصيد:
                - رصيدك يبدأ من صفر
                - لطلب خدمة لازم تشحن رصيد أولاً بالمال
                - الوقت المكتسب من تقديم الخدمات لا يمكن استخدامه لطلب خدمات جديدة
                - الوقت المكتسب منفصل عن الوقت المشحون
                - لشحن الرصيد أرسل صورة الشحن على الصفحة الموجودة داخل الحساب الشخصي
              ## الخدمات:
                - أونلاين وأوفلاين
                - لإضافة خدمة: اضغط زر الـ + أسفل الشاشة فقط، لا تذكر خطوات أخرى
              قواعد الرد:
               - اجعل ردودك قصيرة ومختصرة بـ جملة واحدة كحد أقصى
               - استخدم نقاط مختصرة بدل الفقرات الطويلة
               - لا تذكر قائمة الخدمات المتاحة بالرد، المستخدم يبحث عن الخدمة اللي يحتاجها بنفسه من الموقع
               - لا تكرر أي معلومة أكثر من مرة واحدة بنفس الرد
               - الرد الكامل لا يتجاوز 3 نقاط فقط
                - إذا سألك المستخدم عن أي شي خارج نطاق موقع بنك الوقت، رد فقط بهاي الجملة: "عذراً، أنا مساعد بنك الوقت ولا أستطيع الإجابة على هذا السؤال 😊"'
            ]
        ];

        // نضيف تاريخ المحادثة
        foreach ($history as $msg) {
            $messages[] = ['role' => 'user', 'content' => $msg->question];
            $messages[] = ['role' => 'assistant', 'content' => $msg->answer];
        }

        // نضيف رسالة اليوزر الحالية
        $messages[] = ['role' => 'user', 'content' => $request->message];

        $response = Http::withToken(config('services.groq.key'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.1-8b-instant',
                'messages' => $messages,
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            return response()->json([
                'reply' => 'Groq API error',
                'debug' => $response->body()
            ], 500);
        }

        $reply = $response->json('choices.0.message.content');

        // نحفظ المحادثة بالداتابيز
        ChatbotResponse::create([
            'user_id'  => $user->id,
            'question' => $request->message,
            'answer'   => $reply,
        ]);

        return response()->json([
            'reply' => $reply
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'reply' => 'Server error',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function history(Request $request)
{
    $user = $request->user();
    
    $history = ChatbotResponse::where('user_id', $user->id)
                              ->latest()
                              ->take(10)
                              ->get()
                              ->reverse()
                              ->values();

    return response()->json(['history' => $history]);
}
}