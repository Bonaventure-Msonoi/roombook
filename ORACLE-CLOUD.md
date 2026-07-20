# Deploy RoomBook on Oracle Cloud (Always Free)

This guide gets RoomBook running on Oracle's **Always Free** ARM VM using Docker.

## What you need

- Oracle Cloud account ([signup](https://www.oracle.com/cloud/free/))
- Your RoomBook project on GitHub (or copy files manually)
- ~30 minutes

## Step 1 — Create the VM

1. Log in to [Oracle Cloud Console](https://cloud.oracle.com/)
2. **Compute → Instances → Create instance**
3. Recommended settings:

| Setting | Value |
|---------|-------|
| Name | `roombook` |
| Image | **Ubuntu 22.04** or **24.04** |
| Shape | **Ampere** → `VM.Standard.A1.Flex` (1 OCPU, 6 GB RAM) |
| Boot volume | 50 GB |
| SSH key | Upload your public key (or let Oracle generate one) |

4. **Create**

5. Note the **Public IP address** once the instance is running.

## Step 2 — Open firewall ports

### A) Oracle Security List (required)

1. **Networking → Virtual cloud networks** → your VCN
2. **Security Lists → Default Security List → Add Ingress Rules**

| Source CIDR | Protocol | Port |
|-------------|----------|------|
| `0.0.0.0/0` | TCP | 22 |
| `0.0.0.0/0` | TCP | 80 |

> For internal use only, replace `0.0.0.0/0` with your office IP range.

### B) Ubuntu firewall on the VM

```bash
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 22 -j ACCEPT
sudo netfilter-persistent save
```

## Step 3 — SSH into the VM

```bash
ssh ubuntu@YOUR_PUBLIC_IP
```

## Step 4 — Deploy RoomBook

### Option A — From GitHub (recommended)

```bash
export REPO_URL="https://github.com/YOUR_USER/roombook.git"
curl -fsSL https://raw.githubusercontent.com/YOUR_USER/roombook/main/deploy/oracle-bootstrap.sh -o bootstrap.sh
chmod +x bootstrap.sh
./bootstrap.sh
```

Or clone manually:

```bash
git clone https://github.com/YOUR_USER/roombook.git ~/roombook
cd ~/roombook
cp .env.oracle.example .env
nano .env   # set APP_URL, DB_PASSWORD, DB_ROOT_PASSWORD
bash deploy/oracle-bootstrap.sh
```

### Option B — Copy files from your PC

```bash
# From your Windows machine (PowerShell):
scp -r C:\Users\bonnie\Documents\GitHub\roombook ubuntu@YOUR_PUBLIC_IP:~/roombook
```

Then on the VM:

```bash
cd ~/roombook
cp .env.oracle.example .env
nano .env
sudo docker compose -f docker-compose.prod.yml up -d --build
sudo docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force
```

## Step 5 — Configure `.env`

Edit `~/roombook/.env`:

```env
APP_URL=http://YOUR_PUBLIC_IP
APP_DEBUG=false

DB_PASSWORD=choose_a_strong_password
DB_ROOT_PASSWORD=choose_another_strong_password
```

Generate `APP_KEY` if empty:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan key:generate --force
```

## Step 6 — Verify

Open in a browser:

```
http://YOUR_PUBLIC_IP
```

Login:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@roombook.local` | `password` |
| Staff | `user@roombook.local` | `password` |

**Change these passwords before real use.**

## Useful commands

```bash
cd ~/roombook

# View logs
sudo docker compose -f docker-compose.prod.yml logs -f

# Restart
sudo docker compose -f docker-compose.prod.yml restart

# Update after code changes
git pull
sudo docker compose -f docker-compose.prod.yml up -d --build

# Run artisan
sudo docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

## HTTPS (optional, recommended)

Install Caddy or Certbot on the VM, or put Cloudflare in front of your domain.

With a domain pointing to your VM IP:

```bash
sudo apt install certbot
sudo certbot certonly --standalone -d roombook.yourcompany.com
```

Then update nginx config to use SSL.

## Cost

Oracle **Always Free** tier covers this VM if you stay within free limits (1–4 OCPUs Ampere, 200 GB storage). **$0/month** when within limits.

## Security checklist

- [ ] Change default user passwords
- [ ] Set `APP_DEBUG=false`
- [ ] Restrict Security List to your org IP if possible
- [ ] Disable public registration (already off by default with Breeze — remove register route in production)
- [ ] Plan Entra ID SSO later for real org login

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Can't reach site | Check Oracle Security List port 80 + VM iptables |
| 500 error | `docker compose logs app` — usually missing `APP_KEY` or DB creds |
| DB connection failed | Ensure `.env` uses `DB_HOST=db` (Docker service name) |
| Build fails on ARM | Images used here support `arm64` |
