# Supabase + Docker: habilitar IPv6

O banco Supabase (conexão direta) usa **IPv6**. Se a aplicação roda **dentro do Docker** e aparece "Network is unreachable", o container não está conseguindo usar IPv6.

## Opção 1: Habilitar IPv6 no Docker Desktop (Windows)

1. Abra **Docker Desktop** → **Settings** (engrenagem) → **Docker Engine**.
2. No JSON, adicione ou altere para incluir:
   ```json
   {
     "ipv6": true,
     "fixed-cidr-v6": "fd00::/80"
   }
   ```
   (Se já existir outras chaves, só inclua `"ipv6": true` e `"fixed-cidr-v6": "fd00::/80"`.)
3. **Apply & Restart**.
4. Reinicie os containers:
   ```bash
   docker compose -f docker-compose.dev.yml down
   docker compose -f docker-compose.dev.yml up -d
   ```

## Opção 2: Rodar a aplicação fora do Docker (como o DBeaver)

Se o DBeaver conecta no Supabase na sua máquina, a rede do **host** tem IPv6. Rode a aplicação no host em vez do container:

```bash
cd c:\Users\fabio\Documents\projetos\robson\soprender
php artisan serve
```

Acesse `http://localhost:8000`. O `.env` já está com o Supabase; não use o Docker para o app nesse caso.

## Resumo

- **DBeaver funciona** → sua máquina consegue IPv6.
- **App no Docker falha** → container sem IPv6: use Opção 1 ou Opção 2.
- **Plano gratuito Supabase** → não tem add-on IPv4 nem pooler no dashboard; usar IPv6 (Opção 1 ou 2) resolve.
