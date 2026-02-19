# Documentação

## Conceitos

### RPS (Recibo Provisório de Serviço)

Número interno definido pela aplicação cliente para identificar a nota fiscal antes da emissão.

- Controlado pela aplicação cliente, não pelo órgão emissor.
- Deve ser único em conjunto com a série após a nota ser gerada.
- Não precisa ser sequencial.
- Em caso de erro na emissão, o mesmo RPS pode ser reutilizado. Um novo RPS só é necessário após emissão bem-sucedida.

> **Neste pacote**, o RPS é o próprio `id` do model `PaymentNfse`. Não existe coluna separada — o `id` auto-incrementado da tabela `payment_nfses` é enviado como número do RPS no payload da requisição e utilizado para localizar o registro via webhook.

### Número da Nota Fiscal

Número gerado pelo órgão emissor (AN) de forma **sequencial e automática**, independente do RPS informado.

| RPS enviado | Nº da nota retornado |
|-------------|----------------------|
| 10          | 1                    |
| 2           | 2                    |

---

## Fluxo de Emissão

A emissão de NFS-e é **assíncrona**: o número da nota não é retornado na chamada inicial, mas sim via webhook.

1. **Requisição** — O cliente envia o RPS no payload:
   ```json
   { "identificacao": { "numero": 10, "serie": "...", "tipo": "..." } }
   ```

2. **Processamento** — O AN processa a requisição de forma assíncrona.

3. **Webhook** — O AN notifica com o resultado:
   ```json
   { "status": "processado", "nfse": { "numero": 1, "chave": "..." } }
   ```

> O número da nota só estará disponível após o webhook com `status: processado`.

---

## Resumo

| Campo          | Gerado por         | Sequencial | Único             |
|----------------|--------------------|------------|-------------------|
| RPS            | Aplicação cliente  | Não        | Sim (com a série) |
| Número da nota | Órgão emissor (AN) | Sim        | Sim               |