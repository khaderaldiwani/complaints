<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * عدد المحاولات في حالة الفشل
     */
    public $tries = 3;

    /**
     * الوقت الأقصى لتنفيذ Job (بالثواني)
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     *
     * @param int $userId
     * @param string $title
     * @param string $message
     */
    public function __construct(
        public int $userId,
        public string $title,
        public string $message
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
            NotificationHelper::send(
                $this->userId,
                $this->title,
                $this->message
            );
        } catch (\Exception $e) {
            Log::error('فشل إرسال الإشعار: ' . $e->getMessage(), [
                'user_id' => $this->userId,
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
        Log::error('فشل Job إرسال الإشعار نهائياً', [
            'user_id' => $this->userId,
            'title' => $this->title,
            'error' => $exception->getMessage()
        ]);
    }
}
