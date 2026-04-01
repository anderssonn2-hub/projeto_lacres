# Release Notes v9.25.23

Data: 01/04/2026  
Status: concluido

## Objetivo

Separar a atribuicao de lacres por grupo no fluxo de Correios em lacres_novo.php, para que CAPITAL, CENTRAL IIPR e REGIONAIS possam ser recalculados de forma independente.

## O que mudou

- O botao unico Aplicar Lacres foi substituido por 3 botoes independentes.
- Cada botao recalcula apenas o grupo selecionado.
- O filtro por periodo continua existindo e nao dispara recalculo automaticamente.
- Os inputs de lacre inicial deixaram de ser obrigatorios em conjunto; cada grupo valida apenas o proprio valor ao aplicar.

## Regras mantidas

- CAPITAL: IIPR e Correios em pares com incremento de +2.
- CENTRAL IIPR: IIPR sequencial +1 e Correios igual ao ultimo IIPR + 1 para o grupo.
- REGIONAIS: IIPR e Correios em pares com incremento de +2.

## Validacao esperada

1. Aplicar Capital nao altera CENTRAL IIPR nem REGIONAIS.
2. Aplicar Central nao altera CAPITAL nem REGIONAIS.
3. Aplicar Regionais nao altera CAPITAL nem CENTRAL IIPR.
4. Aplicar Periodo continua apenas filtrando datas.
5. A interface exibe a versao 0.9.25.23.
