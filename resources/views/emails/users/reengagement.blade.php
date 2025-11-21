<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tem novidades no Booknotes esperando por você</title>
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
                                        <h1 style="margin:0;font-size:28px;line-height:1.3;font-weight:600;">Tem novidades no Booknotes esperando por você</h1>
                                        <p style="margin:12px 0 0;font-size:15px;line-height:1.7;color:#e0e7ff;">
                                            Seu espaço de estudos ganhou novos modos e fluxos mais leves.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#0f172a;">
                                Olá{{ $recipientName ? ' '.$recipientName : '' }}! Tudo bem?
                            </p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#334155;">
                                Notamos que você criou sua conta no Booknotes há algum tempo, começou a montar seus cadernos e notas… mas não voltou mais para a plataforma. Sem problemas — isso acontece 🙂
                            </p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#334155;">
                                Queria apenas compartilhar que o Booknotes evoluiu muito nas últimas semanas. Melhoramos a experiência, adicionamos novos modos de estudo, deixamos o fluxo mais simples e seguimos trabalhando com carinho para transformar a plataforma em um espaço cada vez mais útil para sua rotina.
                            </p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#334155;">
                                O Booknotes ainda está no começo da jornada, e justamente por isso seu retorno é muito importante. Se puder, gostaríamos de entender o que fez você se afastar — qualquer detalhe ajuda: dificuldade no uso, falta de tempo, algo confuso na interface ou até mesmo sugestões de recursos. É só responder este e-mail.
                            </p>
                            <div style="margin:24px 0 32px;padding:20px;border:1px solid #e2e8f0;border-radius:20px;background:linear-gradient(135deg,rgba(79,70,229,0.06),rgba(124,58,237,0.05));">
                                <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#0f172a;text-align:center;font-weight:600;">
                                    Seu conteúdo continua salvo por aqui:
                                </p>
                                <p style="margin:0 0 20px;text-align:center;">
                                    <a href="{{ $booknotesUrl }}" style="display:inline-block;padding:15px 30px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#ffffff;font-size:16px;font-weight:600;text-decoration:none;border-radius:999px;">
                                        Voltar para o Booknotes
                                    </a>
                                </p>
                                <p style="margin:0;font-size:14px;line-height:1.6;color:#475569;text-align:center;">
                                    Ou copie e cole este link no navegador:
                                    <br>
                                    <a href="{{ $booknotesUrl }}" style="color:#4f46e5;text-decoration:none;">{{ $booknotesUrl }}</a>
                                </p>
                            </div>
                            <div style="padding:18px 20px;border:1px solid #e2e8f0;border-radius:16px;background-color:#f8fafc;">
                                <p style="margin:0 0 6px;font-size:15px;line-height:1.7;color:#0f172a;font-weight:600;">
                                    Acompanhe as novidades e bastidores
                                </p>
                                <p style="margin:0 0 8px;font-size:14px;line-height:1.6;color:#475569;">
                                    Estamos no Instagram compartilhando lançamentos e o que estamos construindo por aqui.
                                </p>
                                <p style="margin:0;font-size:14px;line-height:1.6;color:#4f46e5;">
                                    📷 <a href="{{ $instagramUrl }}" style="color:#4f46e5;text-decoration:none;">{{ $instagramUrl }}</a>
                                </p>
                            </div>
                            <p style="margin:24px 0 8px;font-size:15px;line-height:1.7;color:#334155;">
                                Muito obrigado por ter feito parte do começo do {{ config('app.name') }}. Esperamos te ver de volta em breve ✨
                            </p>
                            <p style="margin:0;font-size:15px;line-height:1.7;color:#0f172a;font-weight:600;">
                                Abraços,<br>Equipe {{ config('app.name') }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px 40px;">
                            <table width="100%" role="presentation" style="border-top:1px solid #e2e8f0;padding-top:20px;">
                                <tr>
                                    <td style="font-size:13px;color:#94a3b8;line-height:1.6;">
                                        Dúvidas ou feedbacks? Responda este e-mail ou escreva para {{ $supportEmail }}.
                                        <br>
                                        Lembre-se: seus cadernos, disciplinas e notas continuam guardados com segurança.
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
