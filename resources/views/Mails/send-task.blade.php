<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>New Ticket Assigned</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet"/>
</head>
<body style="margin: 0; font-family: 'Poppins', sans-serif; background: #ffffff; font-size: 14px;">
    <table style="max-width: 680px; margin: 0 auto; padding: 45px 30px 60px; background-color: #f4f7ff; color: #434343; width: 100%;">
        <tr>
            <td>
                <table style="width: 100%;">
                    <tr>
                        <td><img src="https://acs.otaavxav.com/email_background/logo_black.png" height="30px" alt="Logo"></td>
                        <td style="text-align: right;"><span style="font-size: 16px;">{{ date('d M, Y') }}</span></td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding: 60px 30px 80px; background: #ffffff; border-radius: 30px;">
                <h2 style="font-size: 24px; font-weight: 600; color: #1f1f1f;">You have a new ticket</h2>
                <p style="margin: 20px 0 0;">Hello,</p>
                <p style="margin: 10px 0 30px;">
                    <strong>{{ $username }}</strong> has assigned a ticket to you. See the ticket details below:
                </p>

                <table style="width: 100%; font-size: 14px; line-height: 1.6;">
                    <tr>
                        <td><strong>From:</strong></td>
                        <td>{{ $username }} ({{ $user_email }})</td>
                    </tr>
                    <tr>
                        <td><strong>Ticket ID:</strong></td>
                        <td>{{ $task_id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Device Serial:</strong></td>
                        <td>{{ $device_id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Subject:</strong></td>
                        <td>{{ $subject }}</td>
                    </tr>
                </table>

                <div style="margin-top: 30px;">
                    <p><strong>Ticket Description:</strong></p>
                    <div style="background: #f4f7ff; padding: 15px; border-radius: 10px; color: #333;">
                        {{ $description }}
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td style="text-align: center; font-weight: 500; color: #8c8c8c; margin-top: 60px;">
                Need help? Contact <a href="mailto:support@avxav.com" style="color: #499fb6;">support@avxav.com</a> or visit our
                <a href="https://avxav.com/telecom" target="_blank" style="color: #499fb6;">Website</a>
            </td>
        </tr>

        <tr>
            <td style="text-align: center; border-top: 1px solid #e6ebf1; padding-top: 40px;">
                <p style="font-size: 16px; font-weight: 600; color: #434343;">@avxavgroup</p>
                <div style="margin-top: 16px;">
                    <a href="https://www.facebook.com/avxavgroup/" target="_blank">
                        <img width="24px" src="https://archisketch-resources.s3.ap-northeast-2.amazonaws.com/vrstyler/1661502815169_682499/email-template-icon-facebook" alt="Facebook"/>
                    </a>
                    <a href="https://www.instagram.com/avxavgroup/" target="_blank" style="margin-left: 8px;">
                        <img width="24px" src="https://archisketch-resources.s3.ap-northeast-2.amazonaws.com/vrstyler/1661504218208_684135/email-template-icon-instagram" alt="Instagram"/>
                    </a>
                </div>
                <p style="margin-top: 16px; color: #434343;">&copy; {{ date('Y') }} AVXAV. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
