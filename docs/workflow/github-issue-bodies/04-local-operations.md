# Цель

Подготовить операционный каркас без production-overkill на раннем dev-этапе.

# Контекст

На dev-этапе не нужен сложный production backup-service. Но healthchecks, scheduler направление и будущий backup/restore регламент забывать нельзя.

# Требования

- Добавить healthcheck endpoint/команду для локального контура.
- Описать scheduler-runner как будущий отдельный сервис для регулярных задач.
- Не поднимать сложный production backup-service на dev-этапе.
- Перед production-пилотом предусмотреть make-команды или регламент для `dump/restore`.

# Критерии приемки

- Есть простой healthcheck.
- В документации понятно, какие операции dev-only, а какие нужны перед production-пилотом.
- Backup не забыт, но не усложняет ранний dev.
