<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();

$periodo = $_GET['periodo'] ?? 'mes';
$inicio = $periodo === 'semana'
    ? date('Y-m-d', strtotime('monday this week'))
    : date('Y-m-01');
$fim = $periodo === 'semana'
    ? date('Y-m-d', strtotime('sunday this week'))
    : date('Y-m-t');

$stmt = db()->prepare("
  SELECT pe.confirmado_em, p.codigo, c.nome_completo, pe.valor, p.status
  FROM pagamentos_extrato pe
  JOIN pedidos p ON p.id = pe.pedido_id
  JOIN clientes c ON c.id = p.cliente_id
  WHERE DATE(pe.confirmado_em) BETWEEN ? AND ?
  ORDER BY pe.confirmado_em ASC
");
$stmt->execute([$inicio, $fim]);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="extrato_' . $periodo . '_' . date('Ymd') . '.csv"');

$out = fopen('php://output', 'w');
// BOM para Excel reconhecer UTF-8
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Data', 'Codigo', 'Cliente', 'Valor', 'Status'], ';');
foreach ($rows as $r) {
    fputcsv($out, [
        date('d/m/Y H:i', strtotime($r['confirmado_em'])),
        $r['codigo'],
        $r['nome_completo'],
        number_format((float)$r['valor'], 2, ',', ''),
        status_label($r['status']),
    ], ';');
}
fclose($out);
exit;
