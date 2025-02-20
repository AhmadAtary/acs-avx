<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>OTP Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
</head>
<body style="margin: 0; font-family: 'Poppins', sans-serif; background: #ffffff; font-size: 14px;">
    <table style="max-width: 680px; margin: 0 auto; padding: 45px 30px 60px; background: #f4f7ff; background-color: #f4f7ff; font-size: 14px; color: #434343; width: 100%;">
        <tr>
            <td>
                <table style="width: 100%;">
                    <tr style="height: 0;">
                        <td>
                            <img alt="Logo webp" src="https://acs.otaavxav.com/email_background/logo_black.png" height="30px" />
                        </td>
                        <td style="text-align: right;">
                            <span style="font-size: 16px; line-height: 30px; color: #0000;">{{ date('d M, Y') }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="margin: 0; margin-top: 70px; padding: 92px 30px 115px; background: #ffffff; border-radius: 30px; text-align: center;">
                <table style="width: 100%; max-width: 489px; margin: 0 auto;">
                    <tr>
                        <td>
                            <h1 style="margin: 0; font-size: 24px; font-weight: 500; color: #1f1f1f;">Your OTP</h1>
                            <p style="margin: 0; margin-top: 17px; font-size: 16px; font-weight: 500;">Dear {{ $user->name }},</p>
                            <p style="margin: 0; margin-top: 17px; font-weight: 500; letter-spacing: 0.56px;">
                                Thank you for signing in. To complete the login process, please use the following OTP (One Time Password) code. The OTP is valid for <span style="font-weight: 600; color: #1f1f1f;">5 minutes</span>. Do not share this code with others.
                            </p>
                            <p style="margin: 0; margin-top: 60px; font-size: 40px; font-weight: 600; letter-spacing: 12.5px; color: #ba3d4f;">{{ $otp_code }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="max-width: 400px; margin: 0 auto; margin-top: 90px; text-align: center; font-weight: 500; color: #8c8c8c;">
                Need help? Ask at <a href="mailto:support@avxav.com" style="color: #499fb6; text-decoration: none;">support@avxav.com</a> or visit our <a href="https://avxav.com/telecom" target="_blank" style="color: #499fb6; text-decoration: none;">Website</a>
            </td>
        </tr>
        <tr>
            <td style="width: 100%; max-width: 490px; margin: 20px auto 0; text-align: center; border-top: 1px solid #e6ebf1;">
                <p style="margin: 0; margin-top: 40px; font-size: 16px; font-weight: 600; color: #434343;">@avxavgroup</p>
                <div style="margin: 0; margin-top: 16px;">
                    <a href="https://www.facebook.com/avxavgroup/" target="_blank" style="display: inline-block;">
                        <img width="24px" alt="Facebook" src="https://archisketch-resources.s3.ap-northeast-2.amazonaws.com/vrstyler/1661502815169_682499/email-template-icon-facebook" />
                    </a>
                    <a href="https://www.instagram.com/avxavgroup/" target="_blank" style="display: inline-block; margin-left: 8px;">
                        <img width="24px" alt="Instagram" src="https://archisketch-resources.s3.ap-northeast-2.amazonaws.com/vrstyler/1661504218208_684135/email-template-icon-instagram" />
                    </a>
                </div>
                <p style="margin: 0; margin-top: 16px; color: #434343;">&copy; {{ date('Y') }} AVXAV. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>