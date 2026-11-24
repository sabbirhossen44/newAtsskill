<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\RedirectTypeEnum;
use Illuminate\Http\RedirectResponse;

/**
 * Port of the old App\Traits\RedirectHelperTrait.
 * Old App\Enums\MessageType / RedirectMessage do not exist in the Phase 1
 * enums, so the message type is the plain 'success' / 'error' string the
 * old trait resolved to (MessageType::SUCCESS / ERROR values).
 */
trait RedirectsWithMessage
{
    private function returnWithMessageAfterCreate($model, $successMessage, $failedMessage, $routeName): RedirectResponse
    {
        $actionStatus = $model->wasRecentlyCreated;
        [$messageType, $message] = $this->getMessages($successMessage, $failedMessage, $actionStatus);

        return $actionStatus
            ? redirect()->route($routeName)->with(['alert-type' => $messageType, 'messege' => $message])
            : redirect()->back()->with(['alert-type' => $messageType, 'messege' => $message]);
    }

    private function returnWithMessageAfterUpdate($model, $successMessage, $failedMessage, $routeName): RedirectResponse
    {
        $actionStatus = $model->wasChanged();
        [$messageType, $message] = $this->getMessages($successMessage, $failedMessage, $actionStatus);

        return $actionStatus
            ? redirect()->route($routeName)->with(['alert-type' => $messageType, 'messege' => $message])
            : redirect()->back()->with(['alert-type' => $messageType, 'messege' => $message]);
    }

    private function getMessages($successMessage, $failedMessage, $actionStatus): array
    {
        return [
            $actionStatus ? 'success' : 'error',
            $actionStatus ? __($successMessage) : __($failedMessage),
        ];
    }

    private function redirectWithMessage(string $type, ?string $route = null, array $params = [], array $notification = []): RedirectResponse
    {
        $messages = [
            RedirectTypeEnum::CREATE->value => 'Created successfully',
            RedirectTypeEnum::UPDATE->value => 'Updated successfully',
            RedirectTypeEnum::DELETE->value => 'Deleted successfully',
            RedirectTypeEnum::ERROR->value => 'Something went wrong',
        ];

        if (! $notification) {
            $notification = [
                'messege' => __($messages[$type] ?? 'Done'),
                'alert-type' => ($type === RedirectTypeEnum::ERROR->value) ? 'error' : 'success',
            ];
        }

        if ($route) {
            return redirect()->route($route, $params)->with($notification);
        }

        return redirect()->back()->with($notification);
    }
}
