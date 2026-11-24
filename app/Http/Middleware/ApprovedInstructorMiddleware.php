<?php

namespace App\Http\Middleware;

use App\Enums\InstructorRequestStatusEnum;
use App\Models\InstructorRequest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedInstructorMiddleware
{
    /**
     * Old logic: redirect to become-instructor unless instructorStatus()
     * was UserStatus::APPROVED. In the new build the approval lives on the
     * InstructorRequest (status enum) and the Instructor record casts
     * status to boolean, so either an approved request or an active
     * instructor record passes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $approved = $user && $user->role === 'instructor' && (
            InstructorRequest::where('user_id', $user->id)
                ->where('status', InstructorRequestStatusEnum::APPROVED->value)->exists()
            || (bool) $user->instructorInfo?->status
        );

        if (! $approved) {
            return redirect()->route('become-instructor');
        }

        return $next($request);
    }
}
