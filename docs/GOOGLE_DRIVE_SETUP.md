# Personal Google Drive OAuth setup

This application stores Admin document uploads in a folder in one personal Google Drive (**My Drive**). An administrator authorizes the Google account once through OAuth 2.0; Laravel then uses the encrypted refresh token to obtain short-lived access tokens automatically.

No service account, JSON key, Google Workspace account, or Shared Drive is used.

## Resulting folder structure

Create one root folder such as `NotebookLM Documents` in My Drive. The application creates publication year and month folders below it:

```text
My Drive/
└── NotebookLM Documents/
    ├── 2026/
    │   ├── 01/
    │   │   └── <uuid>-document-name.pdf
    │   └── 02/
    └── 2027/
```

Generated folder IDs are cached for 30 days. Uploaded filenames receive a UUID prefix and a sanitized name.

## 1. Create or select a Google Cloud project

1. Open the [Google Cloud Console](https://console.cloud.google.com/).
2. Select an existing project or create one for this application.
3. Keep that project selected for every following step.

## 2. Enable the Google Drive API

In the selected project, open **APIs & Services → Library**, find **Google Drive API**, and select **Enable**.

## 3. Configure the OAuth consent screen

Open **Google Auth Platform** in the Cloud Console and configure:

1. **Branding** — set the application name and support email.
2. **Audience** — select **External** when authorizing a personal Gmail account. While the app is in Testing, add the exact Google account as a test user.
3. **Data Access** — add `https://www.googleapis.com/auth/drive`.

The requested `drive` scope allows the application to manage all files in the authorized account and Google classifies it as restricted. Use only a dedicated account whose Drive contents the application is intended to manage. A public production app may require Google verification and, for restricted scopes, a security assessment. If the consent screen stays in Testing, Google states that refresh-token authorization for external test users expires after seven days; move an approved deployment to Production to avoid weekly reconnection.

See Google's [Drive OAuth scopes](https://developers.google.com/workspace/drive/api/guides/api-specific-auth) and [OAuth production-readiness policy](https://developers.google.com/identity/protocols/oauth2/policies).

## 4. Create an OAuth client

Open **Google Auth Platform → Clients → Create client** and select **Web application**.

Add the Laravel callback as an **Authorized redirect URI**. It must exactly match `GOOGLE_DRIVE_REDIRECT_URI`, including scheme, host, port, path, and trailing-slash choice. Examples:

```text
http://localhost:8000/api/admin/google-drive/callback
https://example.com/api/admin/google-drive/callback
```

Use HTTPS in production. Copy the generated Client ID and Client secret into backend environment configuration. Never put the client secret in Vue, a `VITE_` variable, or source control.

Google's [OAuth web-server flow guide](https://developers.google.com/identity/protocols/oauth2/web-server) documents exact redirect matching, offline access, refresh tokens, and state validation.

## 5. Create the My Drive folder

1. Sign in to the personal Google account that will own the uploaded files.
2. In **My Drive**, create `NotebookLM Documents`.
3. Open it and copy the value after `/folders/` from the browser URL.

For example, this URL:

```text
https://drive.google.com/drive/folders/1AbCdEfGhIjKlMnOpQrStUvWxYz
```

has this folder ID:

```text
1AbCdEfGhIjKlMnOpQrStUvWxYz
```

The account authorized in the Admin settings must be able to create and delete content in this folder.

## 6. Configure Laravel

Add these values to `.env`:

```dotenv
GOOGLE_DRIVE_CLIENT_ID=example.apps.googleusercontent.com
GOOGLE_DRIVE_CLIENT_SECRET=replace-with-the-oauth-client-secret
GOOGLE_DRIVE_REDIRECT_URI="${APP_URL}/api/admin/google-drive/callback"
GOOGLE_DRIVE_FOLDER_ID=1AbCdEfGhIjKlMnOpQrStUvWxYz
GOOGLE_DRIVE_SCOPES="https://www.googleapis.com/auth/drive"
GOOGLE_DRIVE_MAX_FILE_SIZE_MB=25
```

The first five variables are required. `GOOGLE_DRIVE_MAX_FILE_SIZE_MB` is optional and defaults to 25 MB.

After changing environment values, refresh Laravel configuration:

```bash
php artisan config:clear
```

For deployments that cache configuration, rebuild the cache only after all production environment values are present:

```bash
php artisan config:cache
```

Run database migrations:

```bash
php artisan migrate
```

Laravel stores OAuth tokens in `google_drive_connections`. Both token columns use Laravel's encrypted model casts, so `APP_KEY` must be configured, protected, backed up, and kept stable. Changing `APP_KEY` makes existing encrypted tokens unreadable and requires reconnecting Google Drive.

## 7. Authorize Google Drive once

1. Sign in to this application as an active Admin.
2. Open **Admin → Google Drive**.
3. Select **Connect Google Drive**.
4. Choose the personal account that owns the configured folder.
5. Review and approve the requested permission.
6. Google redirects to Laravel, which validates a short-lived, single-use state value and exchanges the code on the backend.
7. The browser returns to the Google Drive settings page with a Connected status.

The authorization request uses offline access and explicit consent so Google can issue a refresh token. The tokens never enter the Vue application or its browser storage.

Only authenticated active administrators can start, refresh, or disconnect the integration. The callback itself cannot use the Admin bearer token because Google performs the redirect, so Laravel associates it with the initiating administrator through the one-time OAuth state.

This project uses one global Google Drive connection. `user_id` records which administrator last connected it; all authorized Admin uploads use that connection.

## 8. Upload behavior

The Add Document page is unchanged and accepts PDF, DOCX, PPTX, and TXT files. The server flow is:

```text
Admin upload
    → Sanctum authentication and Admin authorization
    → metadata, relationship, MIME, extension, and size validation
    → database transaction starts
    → current OAuth access token is used or refreshed
    → YYYY/MM folders are found or created in My Drive
    → resumable Drive upload completes
    → Drive ID, name, URL, MIME type, size, and folder ID are saved
    → authors/contributors are attached
    → database transaction commits
```

If database persistence fails after a successful upload, the database transaction rolls back and Laravel deletes the uploaded Drive file. If that compensation deletion also fails, Laravel logs a critical entry with the Drive file ID for operator cleanup. The local temporary upload is always removed.

## 9. Refresh and disconnect

Access tokens are short-lived. Before Drive operations, Laravel checks the stored expiry and automatically exchanges the refresh token when necessary. An Admin can also select **Refresh connection** to test that exchange immediately.

Selecting **Disconnect** asks Google to revoke the current credential and then removes the local encrypted token record. Uploads stop until an Admin reconnects.

## Security checklist

- [ ] `.env` and `APP_KEY` are protected and not committed.
- [ ] The OAuth client secret exists only in backend environment configuration.
- [ ] Access and refresh tokens are never returned to the frontend or written to logs.
- [ ] Production callback and application URLs use HTTPS.
- [ ] Only active Admin routes can connect, refresh, or disconnect.
- [ ] The authorized account is dedicated to the intended document storage when practical.
- [ ] OAuth scopes and consent-screen publication status have been reviewed.
- [ ] Database backups are protected because they contain encrypted credentials.

## Troubleshooting

### `redirect_uri_mismatch`

The URI in Google Cloud and `GOOGLE_DRIVE_REDIRECT_URI` are not identical. Check HTTP versus HTTPS, hostname, port, callback path, and trailing slash. Clear Laravel's configuration cache after correcting `.env`.

### Access denied or authorization cancelled

The user declined consent, the account is not an allowed test user, or an organization policy blocks the requested scope. Add the account under the consent screen's test users or resolve the applicable Google account policy, then connect again.

### Authorization expires after seven days

For an External app in Testing, this is expected Google behavior. Reconnect for development or complete the requirements to publish the consent screen to Production.

### Google did not return a refresh token

Disconnect the app under the Google Account's third-party connections, then use **Connect Google Drive** again. The application requests offline access and forces the consent prompt. Confirm that the callback used the correct OAuth client.

### Refresh token invalid or expired

Laravel removes an unusable connection when Google returns `invalid_grant`. Open **Admin → Google Drive** and reconnect. Common causes include revoked access, password/security changes, Testing-mode expiry, prolonged inactivity, or too many issued refresh tokens.

### Folder not found or permission denied

Confirm `GOOGLE_DRIVE_FOLDER_ID` is the ID of the intended My Drive folder and that the authorized account owns it or can edit it. If a generated year/month folder was moved or deleted, clear the cached folder IDs:

```bash
php artisan cache:clear
```

### Upload initialization or upload failure

Confirm the Drive API is enabled, the connection status is Connected, the server can reach `oauth2.googleapis.com` and `www.googleapis.com`, and PHP/web-server request-size limits allow the file. The API returns controlled application messages rather than Google's raw error payload.

### Database save failed after upload

The application attempts to delete the new Drive file automatically. Search application logs for `Document creation compensation could not delete the Drive file.` If present, use the logged Drive file ID to remove the orphan after fixing access.

## Final verification

- [ ] Google Drive API enabled.
- [ ] OAuth consent screen configured for the account/audience.
- [ ] Web OAuth client created.
- [ ] Exact redirect URI registered.
- [ ] `NotebookLM Documents` created in My Drive.
- [ ] Five required environment values configured.
- [ ] Migrations and configuration refresh completed.
- [ ] Admin connected the correct Google account.
- [ ] Settings page reports Connected.
- [ ] A small allowed file uploaded successfully.
- [ ] Database contains its non-empty `drive_file_id` and metadata.
- [ ] File appears under `NotebookLM Documents/YYYY/MM/`.
- [ ] Disconnect/reconnect procedure is understood by operators.
