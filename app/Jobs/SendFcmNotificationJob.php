<?php

namespace App\Jobs;

use App\Helpers\FcmV1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * عدد المحاولات في حالة الفشل
     */
    public $tries = 3;

    /**
     * الوقت الأقصى لتنفيذ Job (بالثواني)
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     *
     * @param string|int $topic
     * @param string $title
     * @param string $body
     */
    public function __construct(
        public string|int $topic,
        public string $title,
        public string $body
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            FcmV1::sendToTopic($this->topic, $this->title, $this->body);
        } catch (\Exception $e) {
            Log::error('فشل إرسال إشعار FCM: ' . $e->getMessage(), [
                'topic' => $this->topic,
                'title' => $this->title,
                'error' => $e->getMessage()
            ]);
            throw $e; // إعادة رمي الخطأ لإعادة المحاولة
        }
    }

    /**
     * معالجة فشل Job
     */
    public function failed(\Throwable $exception)
    {
        Log::error('فشل Job إرسال FCM نهائياً', [
            'topic' => $this->topic,
            'title' => $this->title,
            'error' => $exception->getMessage()
        ]);
    }
}
