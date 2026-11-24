<?php

namespace App\Http\Controllers\Concerns;

use App\Jobs\SendVerifyMailToUser;
use App\Jobs\sendLiveClassMailJob;
use App\Jobs\sendQnaReplyMailJob;
use App\Jobs\SocialLoginDefaultPasswordJob;
use App\Jobs\UserForgetPasswordJob;
use App\Mail\sendLiveClassMail;
use App\Mail\sendQnaReplyMail;
use App\Mail\SocialLoginDefaultPasswordMail;
use App\Mail\UserForgetPassword;
use App\Mail\UserRegistration;
use App\Models\EmailTemplate;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Port of the mail-sending methods of the old App\Services\MailSenderService
 * (not present in the new app/Services). Templates come from the
 * EmailTemplate model exactly as in the old code.
 */
trait SendsAppMail
{
    use SendsMail;

    public function sendVerifyMailToUserFromTrait($user_type, $user_info = null)
    {
        if (static::setMailConfig()) {
            try {
                if (static::isQueable()) {
                    dispatch(new SendVerifyMailToUser($user_type, $user_info));
                } else {
                    if ($user_type == 'all_user') {
                        $users = User::where('email_verified_at', null)->orderBy('id', 'desc')->get();
                        foreach ($users as $user) {
                            $user->verification_token = Str::random(100);
                            $user->save();
                            try {
                                $template = EmailTemplate::where('name', 'user_verification')->first();
                                $message = str_replace('{{user_name}}', $user->name, $template->message);
                                Mail::to($user->email)->send(new UserRegistration($message, $template->subject, $user));
                            } catch (Exception $ex) {
                                if (app()->isLocal()) {
                                    Log::error($ex->getMessage());
                                }
                            }
                        }
                    } else {
                        try {
                            $template = EmailTemplate::where('name', 'user_verification')->first();
                            $message = str_replace('{{user_name}}', $user_info->name, $template->message);
                            Mail::to($user_info->email)->send(new UserRegistration($message, $template->subject, $user_info));
                        } catch (Exception $ex) {
                            if (app()->isLocal()) {
                                Log::error($ex->getMessage());
                            }
                        }
                    }
                }

                return true;
            } catch (Exception $th) {
                if (app()->isLocal()) {
                    Log::error($th->getMessage());
                }

                return false;
            }
        }

        return false;
    }

    public function sendUserForgetPasswordFromTrait($from_user, $mail_template_path = 'auth')
    {
        if (static::setMailConfig()) {
            try {
                if (static::isQueable()) {
                    dispatch(new UserForgetPasswordJob($from_user, $mail_template_path));
                } else {
                    try {
                        $template = EmailTemplate::where('name', 'password_reset')->first();
                        $message = str_replace('{{user_name}}', $from_user->name, $template->message);
                        Mail::to($from_user->email)->send(new UserForgetPassword($message, $template->subject, $from_user, $mail_template_path));
                    } catch (Exception $ex) {
                        if (app()->isLocal()) {
                            Log::error($ex->getMessage());
                        }
                    }
                }

                return true;
            } catch (Exception $th) {
                if (app()->isLocal()) {
                    Log::error($th->getMessage());
                }

                return false;
            }
        }

        return false;
    }

    public function sendSocialLoginDefaultPasswordFromTrait($user, $password)
    {
        if (static::setMailConfig()) {
            try {
                if (static::isQueable()) {
                    dispatch(new SocialLoginDefaultPasswordJob($user, $password));
                } else {
                    try {
                        Mail::to($user->email)->send(new SocialLoginDefaultPasswordMail($user, $password));
                    } catch (Exception $ex) {
                        if (app()->isLocal()) {
                            Log::error($ex->getMessage());
                        }
                    }
                }

                return true;
            } catch (Exception $th) {
                if (app()->isLocal()) {
                    Log::error($th->getMessage());
                }

                return false;
            }
        }

        return false;
    }

    public function sendQnaReplyMailTrait($question)
    {
        if (static::setMailConfig()) {
            try {
                $template = EmailTemplate::where('name', 'qna_reply_mail')->first();
                $email = $question?->user?->email;
                $message = $template->message;
                $subject = $template->subject;
                $message = str_replace('{{user_name}}', $question?->user?->name, $message);
                $message = str_replace('{{course}}', $question?->course?->title, $message);
                $message = str_replace('{{lesson}}', $question?->lesson?->title, $message);
                $message = str_replace('{{question}}', $question?->question_title, $message);
                $link = route('student.learning.index', ['slug' => $question?->course?->slug, 'lesson' => $question?->lesson_id, 'type' => $question?->lesson?->chapterItem?->type]);

                if (static::isQueable()) {
                    dispatch(new sendQnaReplyMailJob($email, $subject, $message, $link));
                } else {
                    Mail::to($email)->send(new sendQnaReplyMail($subject, $message, $link));
                }

                return true;
            } catch (Exception $th) {
                if (app()->isLocal()) {
                    Log::error($th->getMessage());
                }

                return false;
            }
        }

        return false;
    }

    public function sendLiveClassNotificationMailTrait($users, $data)
    {
        if (static::setMailConfig()) {
            try {
                $template = EmailTemplate::where('name', 'live_class_mail')->first();
                $message = $template->message;
                $subject = $template->subject;
                $message = str_replace('{{course}}', $data?->course, $message);
                $message = str_replace('{{lesson}}', $data?->lesson, $message);
                $message = str_replace('{{start_time}}', $data?->start_time, $message);
                $message = str_replace('{{join_url}}', $data?->join_url, $message);

                foreach ($users as $user) {
                    $email = $user?->email;
                    $userMessage = str_replace('{{user_name}}', $user?->name, $message);
                    if (static::isQueable()) {
                        dispatch(new sendLiveClassMailJob($email, $subject, $userMessage));
                    } else {
                        Mail::to($email)->send(new sendLiveClassMail($subject, $userMessage));
                    }
                }

                return true;
            } catch (Exception $th) {
                if (app()->isLocal()) {
                    Log::error($th->getMessage());
                }

                return false;
            }
        }

        return false;
    }
}
