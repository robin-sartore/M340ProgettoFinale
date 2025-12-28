# ProxMox VM Portal

Portale web per la richiesta e creazione automatica di macchine virtuali su ProxMox VE.

##Descrizione

Sistema che permette agli utenti di richiedere macchine virtuali attraverso un'interfaccia web. Gli amministratori approvano le richieste e le VM vengono create automaticamente su ProxMox tramite API REST.

## Funzionalità Principali

**Autenticazione sicura** con ruoli admin/user
 **Richiesta VM** con selezione template
**Approvazione amministrativa**
 **Creazione automatica** su ProxMox
**Dashboard** con stato richieste e credenziali

## Architettura

- **Frontend**: HTML5, Bootstrap 5.3
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Hypervisor**: ProxMox VE 8.x

## Template VM Disponibili

| Template | CPU | RAM | Disco | Utilizzo |
|----------|-----|-----|-------|----------|
| Bronze   | 1   | 1GB | 8GB  | Sviluppo leggero |
| Silver   | 2   | 4GB | 16GB  | Sviluppo medio |
| Gold     | 4   | 8GB | 32GB  | Produzione |

## Uso

### 1. Prerequisiti
- Apache/Nginx con PHP 8.0+
- MySQL 8.0+
- ProxMox VE 8.x configurato

### 2. Proxmox
- avviare i due nodi del cluster

### Accesso
- **URL**: `http://192.168.56.103`
- **Admin**: `admin` / `admin123`
- **User**: `user` / `user123`

### Workflow
1. **Utente** richiede VM selezionando template
2. **Admin** approva dalla dashboard
3. **Sistema** crea VM automaticamente su ProxMox
4. **Utente** riceve credenziali SSH


## Configurazione
### 1. Prerequisiti
- Apache/Nginx con PHP 8.0+
- MySQL 8.0+
- ProxMox VE 8.x configurato

### 2. Database in vm-Proxmox
```sql
CREATE DATABASE progetto;
SOURCE sql/progett_db_backup.sql;
```

### 3. ProxMox Setup
1. **Crea token API**:
   - Datacenter → Permissions → API Tokens
   - User: `root@pam`
   - Token ID: `token-flask`
   - Salvarsi Token Secret

2. **Prepara template VM** (VMID 1600):
   ```bash
   # Installa agente guest
   sudo apt install qemu-guest-agent
   sudo systemctl enable qemu-guest-agent
   # Converti in template
   ```

### 4. Configurazione
Modifica `config/db.php`:
```php
$proxmox = [
    'host' => '192.168.56.15',
    'user' => 'root@pam',
    'token_id' => 'token-flask',
    'token_secret' => 'IL_TUO_TOKEN_SECRET',
    'node' => 'px1'
];
```

### 5. Web Server

Nella VM Proxmox (console o SSH):
```bash
cd /var/www/html
sudo mv public/* ./
sudo mv public/.* ./ 2>/dev/null || true   # Sposta anche file nascosti
sudo rmdir public   # Elimina la cartella vuota
```

Ora tutti i file sono direttamente in `/var/www/html` e i path sono già corretti.

**Accesso**: `http://192.168.56.103`


### Problemi Comuni

| Problema | Sintomo | Soluzione |
|----------|---------|-----------|
| **No autenticazione** | HTTP 401 | Verifica token ProxMox |
| **VM non si avvia** | Stato stopped | Controlla risorse disponibili |
| **IP non rilevato** | Agente guest errore | Installa `qemu-guest-agent` |
| **Clone fallito** | VM non creata | Verifica template e spazio |


##  Struttura Progetto

```
M340ProgettoFinale/
├── config/
│   └── db.php              # Config DB e ProxMox
├── public/                 # File pubblici
│   ├── login.php          # Login
│   ├── dashboard.php      # Dashboard utente
│   ├── admin.php          # Pannello admin
│   ├── request_vm.php     # Richiesta VM
│   ├── approve.php        # Approvazione (con API ProxMox)
│   └── test_proxmox.php   # Test connessione
├── sql/
│   └── DB.txt             # Schema database
|
README.md
```

##  Sicurezza

- Password hash con bcrypt
- Validazione input server-side
- Token API ProxMox (no password)
- Sessioni sicure
- Limitazione tentativi login



---

**Progetto M340 - Sistemi Operativi e Reti**  
*Realizzato da Robin Sartore*
