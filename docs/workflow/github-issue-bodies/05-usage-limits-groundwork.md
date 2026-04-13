# Цель

Заложить будущий учет ресурсов по tenant/store без полноценного billing engine.

# Контекст

Managed Store не должен быть безлимитным. На MVP нужно заложить понимание будущих метрик: storage, API requests, static traffic, backups, external services.

# Требования

- Подготовить модель или backlog для `resource_usage_daily` и `store_usage_limits`.
- Логировать tenant/store context для значимых действий.
- Не показывать клиенту технические CPU/DB лимиты на MVP.
- Подготовить основу для будущих метрик: storage, API requests, static egress, backups.

# Критерии приемки

- В коде и документации понятно, как потом считать usage по store/tenant.
- Нет обещания безлимитного shared-контура.
