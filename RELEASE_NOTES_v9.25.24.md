# Release Notes v9.25.24

## Objetivo

Permitir que a Central IIPR use 3 ou mais malotes no split, cada um com seu proprio lacre Correios e seu proprio display dos Correios, sem que um grupo sobrescreva o outro.

## Alteracoes

- O split visual da Central agora trata todos os cortes marcados, nao apenas o primeiro.
- Cada bloco entre splits passa a ter sua propria linha editavel de referencia para lacre Correios e etiqueta Correios.
- A replicacao automatica ocorre somente dentro do bloco correspondente.
- A sincronizacao antes de imprimir respeita todos os blocos gerados pelo split.
- Versao exibida em lacres_novo.php atualizada para 0.9.25.24.

## Resultado esperado

- Sem split: a Central continua funcionando como um unico malote.
- Com 1 split: continuam existindo 2 malotes independentes.
- Com 2 splits: passam a existir 3 malotes independentes.
- Com N splits validos: passam a existir N+1 malotes independentes.

## Validacao sugerida

1. Na Central IIPR, marcar dois pontos de split para formar 3 grupos.
2. Digitar um lacre Correios diferente na primeira linha de cada grupo.
3. Confirmar que apenas as linhas do mesmo grupo recebem o valor digitado.
4. Repetir o teste com etiquetas Correios.
5. Salvar e imprimir para verificar que cada malote manteve seu proprio valor.