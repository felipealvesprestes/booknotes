<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirme seu e-mail para liberar o Booknotes</title>
</head>
<body style="margin:0;padding:0;background-color:#0f172a;font-family:'Inter',Arial,sans-serif;color:#0f172a;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#0f172a;padding:32px 0;">
        <tr>
            <td align="center">
                <table width="640" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#ffffff;border-radius:28px;overflow:hidden;box-shadow:0 25px 60px rgba(15,23,42,0.25);">
                    <tr>
                        <td style="padding:32px 40px 16px 40px;background:linear-gradient(135deg,#4f46e5,#312e81);color:#f8fafc;">
                            <table width="100%" role="presentation">
                                <tr>
                                    <td style="font-size:18px;font-weight:600;letter-spacing:0.2em;text-transform:uppercase;">{{ config('app.name') }}</td>
                                    <td align="right">
                                        <svg width="36" height="36" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="48" height="48" rx="12" fill="rgba(255,255,255,0.15)"/>
                                            <path d="M33 14H15a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h18a3 3 0 0 0 3-3V17a3 3 0 0 0-3-3Zm-9 12L15 17h18l-9 9Z" fill="#f8fafc" />
                                        </svg>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding-top:28px;">
                                        <h1 style="margin:0;font-size:28px;line-height:1.3;font-weight:600;">Confirme seu e-mail para utilizar o Booknotes</h1>
                                        <p style="margin:12px 0 0;font-size:15px;line-height:1.7;color:#e0e7ff;">
                                            Mantenha seu hub de estudos protegido e pronto em qualquer dispositivo.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#0f172a;">
                                Olá {{ $user->name }},
                            </p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#334155;">
                                Toque no botão abaixo para confirmar seu endereço e concluir a criação da sua conta Booknotes. O link fica ativo por 60 minutos.
                            </p>

                            <p style="margin:0 0 30px;text-align:center;">
                                <a href="{{ $verifyUrl }}" style="display:inline-block;padding:16px 32px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#ffffff;font-size:16px;font-weight:600;text-decoration:none;border-radius:999px;">
                                    Confirmar e-mail
                                </a>
                            </p>

                            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#475569;">
                                Se o botão não funcionar, copie e cole este link no navegador:
                            </p>
                            <p style="word-break:break-all;font-size:13px;line-height:1.6;color:#6366f1;margin:0 0 24px;">
                                <a href="{{ $verifyUrl }}" style="color:#4f46e5;text-decoration:none;">{{ $verifyUrl }}</a>
                            </p>

                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;">
                                Se você não fez essa solicitação, pode ignorar este e-mail com segurança.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px 40px;">
                            <table width="100%" role="presentation" style="border-top:1px solid #e2e8f0;padding-top:20px;">
                                <tr>
                                    <td style="font-size:13px;color:#94a3b8;line-height:1.6;">
                                        Dúvidas? Responda este e-mail ou escreva para {{ $supportEmail }}
                                        <br>
                                        Essa verificação protege seu workspace e garante que só você pode acessar suas notas.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <p style="margin:20px 0 0;font-size:12px;color:#94a3b8;font-family:'Inter',Arial,sans-serif;">
                    © {{ date('Y') }} {{ config('app.name') }} • Todos os direitos reservados.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
