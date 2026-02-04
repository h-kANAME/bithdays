<?php
/**
 * Servicio de Email usando EmailJS
 * Prepara los datos para envío mediante EmailJS desde el frontend
 */

class EmailService {
    private $serviceId;
    private $templateId;
    private $publicKey;

    public function __construct() {
        $this->serviceId = getenv('EMAILJS_SERVICE_ID') ?: '';
        $this->templateId = getenv('EMAILJS_TEMPLATE_ID') ?: '';
        $this->publicKey = getenv('EMAILJS_PUBLIC_KEY') ?: '';
    }

    /**
     * Preparar datos para notificación de cumpleaños
     * EmailJS se ejecutará desde el frontend o mediante cURL
     */
    public function sendBirthdayNotification($destinatarios, $usuariosCumpleanios) {
        if (empty($this->serviceId) || empty($this->templateId) || empty($this->publicKey)) {
            return [
                'success' => false,
                'message' => 'Configuración de EmailJS incompleta',
                'error' => 'EMAILJS_NOT_CONFIGURED'
            ];
        }

        if (empty($destinatarios)) {
            return [
                'success' => false,
                'message' => 'No hay destinatarios configurados',
                'error' => 'NO_RECIPIENTS'
            ];
        }

        if (empty($usuariosCumpleanios)) {
            return [
                'success' => false,
                'message' => 'No hay cumpleaños para notificar',
                'error' => 'NO_BIRTHDAYS'
            ];
        }

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        // Preparar lista de cumpleaños
        $cumpleaniosList = $this->formatBirthdayList($usuariosCumpleanios);

        // Enviar email a cada destinatario
        foreach ($destinatarios as $destinatario) {
            $result = $this->sendEmail(
                $destinatario['email'],
                $destinatario['nombre'] . ' ' . $destinatario['apellido'],
                $cumpleaniosList,
                $usuariosCumpleanios
            );

            $results[] = [
                'destinatario' => $destinatario['email'],
                'success' => $result['success'],
                'message' => $result['message']
            ];

            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'message' => "Emails enviados: $successCount, Errores: $errorCount",
            'count' => count($usuariosCumpleanios),
            'recipients' => count($destinatarios),
            'successCount' => $successCount,
            'errorCount' => $errorCount,
            'details' => $results
        ];
    }

    /**
     * Enviar email individual mediante EmailJS API usando cURL
     */
    private function sendEmail($to, $toName, $cumpleaniosList, $cumpleaniosData) {
        $url = 'https://api.emailjs.com/api/v1.0/email/send';

        $data = [
            'service_id' => $this->serviceId,
            'template_id' => $this->templateId,
            'user_id' => $this->publicKey,
            'template_params' => [
                'to_email' => $to,
                'to_name' => $toName,
                'fecha_actual' => date('d/m/Y'),
                'cumpleanios_list' => $cumpleaniosList
            ]
        ];

        $jsonData = json_encode($data);

        try {
            // Log de debug
            error_log("EmailJS Request URL: " . $url);
            error_log("EmailJS Request Data: " . $jsonData);

            // Usar cURL para simular un navegador (headers completos para evitar bloqueo de EmailJS)
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: application/json, text/plain, */*',
                'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
                'Accept-Encoding: gzip, deflate, br',
                'Origin: https://kyz.com.ar',
                'Referer: https://kyz.com.ar/birthdays/',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: cross-site',
                'sec-ch-ua: "Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_ENCODING, ''); // Habilitar decompresión automática

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            error_log("EmailJS HTTP Code: " . $httpCode);
            error_log("EmailJS Response: " . $result);

            if ($result === false) {
                error_log("cURL Error: " . $curlError);
                return [
                    'success' => false,
                    'message' => 'Error al conectar con EmailJS: ' . $curlError
                ];
            }

            // Verificar código HTTP
            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'message' => 'Email enviado correctamente'
                ];
            } else if ($httpCode === 400) {
                error_log("EmailJS 400 Bad Request: " . $result);
                return [
                    'success' => false,
                    'message' => 'EmailJS: Datos inválidos o template mal configurado'
                ];
            } else if ($httpCode === 401 || $httpCode === 403) {
                return [
                    'success' => false,
                    'message' => 'EmailJS: Credenciales inválidas'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'EmailJS error (HTTP ' . $httpCode . '): ' . $result
                ];
            }

        } catch (Exception $e) {
            error_log("EmailService exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Formatear lista de cumpleaños para el email
     */
    private function formatBirthdayList($usuarios) {
        $list = [];

        foreach ($usuarios as $usuario) {
            $edad = $this->calculateAge($usuario['fecha_nacimiento']);
            $obs = !empty($usuario['observaciones']) ? ' - ' . $usuario['observaciones'] : '';

            $list[] = sprintf(
                "%s %s (%d años)%s",
                $usuario['nombre'],
                $usuario['apellido'],
                $edad,
                $obs
            );
        }

        return implode("\n", $list);
    }

    /**
     * Calcular edad desde fecha de nacimiento
     */
    private function calculateAge($fechaNacimiento) {
        $birthDate = new DateTime($fechaNacimiento);
        $today = new DateTime('today');
        return $birthDate->diff($today)->y;
    }

    /**
     * Obtener configuración de EmailJS
     */
    public function getConfig() {
        return [
            'configured' => !empty($this->serviceId) && !empty($this->templateId) && !empty($this->publicKey),
            'serviceId' => $this->serviceId,
            'templateId' => $this->templateId,
            'publicKey' => $this->publicKey
        ];
    }

    /**
     * Enviar email de prueba
     */
    public function sendTestEmail($testEmail = null) {
        $testRecipient = $testEmail ?: 'test@example.com';

        $testData = [
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'fecha_nacimiento' => '1990-01-01',
                'observaciones' => 'Usuario de prueba'
            ]
        ];

        $destinatarios = [
            [
                'email' => $testRecipient,
                'nombre' => 'Usuario',
                'apellido' => 'Prueba'
            ]
        ];

        return $this->sendBirthdayNotification($destinatarios, $testData);
    }
}
