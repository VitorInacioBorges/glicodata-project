# Boas práticas

## Segurança e privacidade

- Derive `ubs_id` da conta autenticada; nunca aceite tenant clínico do payload.
- Use `validated()` e API Resources com campos explícitos.
- Mantenha profissionais como referências não autenticáveis.
- Armazene senha somente para UBS e administrador, com Argon2id.
- Não registre valores pessoais ou clínicos em logs e auditoria.
- Rejeite campos removidos com `prohibited`, em vez de ignorá-los silenciosamente.
- Faça exportações apenas agregadas e suprima grupos com menos de cinco registros.

## Fluxo de domínio

- Paciente e profissional de uma avaliação devem pertencer à mesma UBS.
- Apenas profissionais ativos podem ser selecionados.
- Questionários publicados não são reescritos; mudanças criam nova versão.
- O cliente envia respostas, nunca score/classificação confiáveis.
- Avaliações concluídas são imutáveis e riscos não possuem endpoints de escrita.
- Exclusões clínicas são lógicas e auditadas.

## Frontend

- Use `@vite` no layout e mantenha `public/hot` apenas durante `npm run dev`.
- Em homologação/produção, compile `public/build` e sirva tudo pela origem do Laravel.
- JavaScript dinâmico deve preservar validação no servidor e construir texto com `textContent`.
- Campos removidos do backend também devem desaparecer das views e mensagens.

## Verificação

```bash
vendor/bin/pint --test
php artisan test
npm run build
php artisan route:list --except-vendor
composer audit
npm audit
```

Use SQLite em memória nos testes. Antes de `migrate:fresh`, valide explicitamente o nome do PostgreSQL conectado.
