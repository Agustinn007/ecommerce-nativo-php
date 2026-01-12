<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$details = $data['details'] ?? '';

if (!$name) {
    echo json_encode(['description' => '']);
    exit;
}

// Detectar contexto de perfume y volumen
$isPerfume = stripos($name, 'perfume') !== false || stripos($details, 'perfume') !== false;
$hasMl = stripos($details, 'ml') !== false;

// --- MODO SIMULACIÓN (Gratis y Rápido) ---
// Genera descripciones convincentes basadas en plantillas
if ($isPerfume && $hasMl) {
    $templates = [
        "Disfruta de la esencia única de $name. Esta presentación de $details es perfecta para quienes buscan elegancia y duración en su día a día.",
        "Descubre $name, una fragancia cautivadora que viene en un envase de $details. Ideal para regalar o para darte un gusto personal.",
        "El nuevo $name combina notas exquisitas en un formato de $details, diseñado para acompañarte en cada momento especial."
    ];
} elseif ($details) {
    $templates = [
        "Descubre el nuevo $name. Se caracteriza especialmente por $details, lo que lo convierte en una opción única en el mercado. Diseñado pensando en tu satisfacción.",
        "Si buscas $details, el $name es perfecto para ti. Combina calidad y funcionalidad para ofrecerte la mejor experiencia.",
        "Presentamos $name, destacando por $details. Un producto innovador que no puede faltar en tu colección."
    ];
} else {
    $templates = [
        "Descubre el nuevo $name, diseñado para quienes buscan calidad y estilo en un solo producto. Fabricado con los mejores materiales, garantiza durabilidad y un acabado premium que te encantará.",
        "Eleva tu experiencia con $name. La combinación perfecta de funcionalidad y diseño moderno. Ideal para el uso diario o para regalar a esa persona especial.",
        "¡No te pierdas $name! Un producto exclusivo que destaca por su elegancia y versatilidad. Aprovecha esta oportunidad para tener lo mejor del mercado en tus manos."
    ];
}
$desc = $templates[array_rand($templates)];

// --- MODO REAL (Requiere API Key de OpenAI) ---
/*
$apiKey = 'TU_CLAVE_API_AQUI';
$prompt = "Escribe una descripción de venta atractiva y corta para un producto llamado: $name. " . ($details ? "Detalles clave a incluir: $details." : "") . ($isPerfume && $hasMl ? " Menciona explícitamente la presentación de $details." : "");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-3.5-turbo",
    "messages" => [["role" => "user", "content" => $prompt]],
    "max_tokens" => 100
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
// ... procesar respuesta de curl ...
*/

echo json_encode(['description' => $desc]);
?>