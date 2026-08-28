# LYNERA SaaS Scaling Readiness

> Smart Tools for Modern Makeup Artists

## Multi Team
- Introduce `teams` and `team_members` tables.
- Support role matrix per tenant (owner, manager, staff).
- Move `tenant_id` to `team_id` where applicable.

## Mobile API
- Expose `/api/v1` endpoints with token auth.
- Keep tenant isolation via scope + policy.
- Add API resources/transformers for stable contracts.

## Webhooks
- Outbound webhook subscriptions table.
- Queue-based delivery workers.
- Signature validation and replay protection.

## Plan Governance
- Tetapkan `config/plans.php` sebagai satu-satunya sumber aturan paket.
- Hindari hardcode harga/limit/fitur di controller atau blade.
- Simpan perubahan paket sebagai changelog produk agar tim support bisa menjelaskan perubahan ke user.
- Tambahkan regression test setiap kali ada perubahan limit/feature flag paket.

