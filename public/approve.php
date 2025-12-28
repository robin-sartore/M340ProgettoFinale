<?php
set_time_limit(600);

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    exit("Accesso negato");
}

require "config/db.php";

$id = (int)$_GET['id'];

/* DATI RICHIESTA */
$stmt = $db->prepare("
    SELECT r.*, t.cpu, t.ram, t.disk, t.proxmox_template_id
    FROM vm_requests r
    JOIN vm_templates t ON r.template_id = t.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    die("Richiesta non trovata");
}

/* API PROXMOX */
function proxmox_api($p, $method, $endpoint, $data = []) {
    $url = "https://{$p['host']}:8006/api2/json/$endpoint";
    $headers = ["Authorization: PVEAPIToken={$p['user']}!{$p['token_id']}={$p['token_secret']}"];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 60
    ]);
    if (in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $code >= 400) {
        error_log("API error: $err | Code $code | Response: $res");
        return false;
    }
    return json_decode($res, true);
}

/* VMID LIBERO */
$vmid = 500;
do {
    $check = proxmox_api($proxmox, 'GET', "nodes/{$proxmox['node']}/qemu/$vmid/status/current");
    $vmid++;
} while (isset($check['data']));
$vmid--;

/* CLONE */
$clone = proxmox_api($proxmox, 'POST',
    "nodes/{$proxmox['node']}/qemu/{$request['proxmox_template_id']}/clone",
    [
        'newid' => $vmid,
        'name' => $request['hostname'],
        'full' => 1,
        'target' => $proxmox['node']
    ]
);

if (!$clone || !isset($clone['data'])) {
    die("Errore clonazione");
}

/* ATTENDI CLONE */
$upid = $clone['data'];
for ($i = 0; $i < 40; $i++) {
    sleep(5);
    $status = proxmox_api($proxmox, 'GET', "nodes/{$proxmox['node']}/tasks/$upid/status");
    if ($status && $status['data']['status'] === 'stopped') break;
}

/* CONFIGURA RISORSE */
proxmox_api($proxmox, 'POST', "nodes/{$proxmox['node']}/qemu/$vmid/config", [
    'cores' => $request['cpu'],
    'memory' => $request['ram']
]);

proxmox_api($proxmox, 'PUT', "nodes/{$proxmox['node']}/qemu/$vmid/resize", [
    'disk' => 'scsi0',
    'size' => $request['disk'] . 'G'
]);

/* AVVIA VM */
proxmox_api($proxmox, 'POST', "nodes/{$proxmox['node']}/qemu/$vmid/status/start");

/* ATTENDI IP (MAX 3 MIN) */
$vm_ip = "In attesa (aggiorna tra 1-2 min)";
for ($i = 0; $i < 18; $i++) {
    sleep(10);
    $net = proxmox_api($proxmox, 'GET', "nodes/{$proxmox['node']}/qemu/$vmid/agent/network-get-interfaces");
    if ($net && isset($net['data']['result'])) {
        foreach ($net['data']['result'] as $iface) {
            if (isset($iface['ip-addresses'])) {
                foreach ($iface['ip-addresses'] as $addr) {
                    if ($addr['ip-address-type'] === 'ipv4' && $addr['ip-address'] !== '127.0.0.1') {
                        $vm_ip = $addr['ip-address'];
                        goto ip_found;
                    }
                }
            }
        }
    }
}
ip_found:

/* UTENTE E PASSWORD FISSI */
$vm_user = "ubuntu";
$vm_password = "Password/1";

/* AGGIORNA DB */
$update = $db->prepare("
    UPDATE vm_requests
    SET status='approved', vmid=?, ip=?, vm_password=?, vm_username=?
    WHERE id=?
");
$update->execute([$vmid, $vm_ip, $vm_password, $vm_user, $id]);

header("Location: admin.php?success=1");
exit;
?>