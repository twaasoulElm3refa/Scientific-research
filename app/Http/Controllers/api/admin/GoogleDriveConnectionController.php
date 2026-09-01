<?php

namespace App\Http\Controllers\api\admin;

use App\Exceptions\GoogleDriveException;
use App\Http\Controllers\Controller;
use App\Services\GoogleDriveOAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleDriveConnectionController extends Controller
{
    public function status(GoogleDriveOAuthService $oauth): JsonResponse
    {
        $connection = $oauth->connection();

        return response()->json([
            'connected' => $connection !== null && filled($connection->refresh_token),
            'connected_at' => $connection?->created_at?->toIso8601String(),
            'updated_at' => $connection?->updated_at?->toIso8601String(),
            'expires_at' => $connection?->expires_at?->toIso8601String(),
            'connected_by' => $connection?->user ? [
                'id' => $connection->user->id,
                'name' => $connection->user->name,
                'email' => $connection->user->email,
            ] : null,
        ]);
    }

    public function authorize(Request $request, GoogleDriveOAuthService $oauth): JsonResponse
    {
        try {
            return response()->json([
                'authorization_url' => $oauth->authorizationUrl($request->user()),
            ]);
        } catch (GoogleDriveException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function callback(Request $request, GoogleDriveOAuthService $oauth): RedirectResponse
    {
        $state = $request->string('state')->toString();

        if ($state === '') {
            return $this->settingsRedirect('invalid-state');
        }

        try {
            $admin = $oauth->consumeState($state);

            if ($request->query('error')) {
                return $this->settingsRedirect('denied');
            }

            $code = $request->string('code')->toString();

            if ($code === '') {
                return $this->settingsRedirect('failed');
            }

            $oauth->connect($admin, $code);

            return $this->settingsRedirect('connected');
        } catch (GoogleDriveException) {
            return $this->settingsRedirect('failed');
        }
    }

    public function refresh(GoogleDriveOAuthService $oauth): JsonResponse
    {
        try {
            $oauth->accessToken(true);

            return response()->json(['message' => 'Google Drive connection refreshed successfully.']);
        } catch (GoogleDriveException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(GoogleDriveOAuthService $oauth): JsonResponse
    {
        try {
            $oauth->disconnect();

            return response()->json(['message' => 'Google Drive disconnected successfully.']);
        } catch (GoogleDriveException $exception) {
            return response()->json(['message' => $exception->getMessage()], 502);
        }
    }

    private function settingsRedirect(string $result): RedirectResponse
    {
        $baseUrl = rtrim((string) config('app.url'), '/');

        return redirect()->away($baseUrl.'/admin/settings/google-drive?google_drive='.urlencode($result));
    }
}
