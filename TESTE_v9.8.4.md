# 🧪 TESTE v9.8.4 - Debug e Diagnóstico de Problema

## 📅 Data: 26/01/2026

## 🎯 Problema Reportado

**Relato:**
> "eu cliquei no botão Gerar oficio poupa tempo e não apareceu nada no oficio poupa tempo, não apareceu os lotes discriminados"

## 🔍 Correções Aplicadas

### 1. Debug Detalhado Adicionado
Agora é possível ver EXATAMENTE o que está acontecendo.

### 2. Mensagem Clara de Erro
Se não houver dados, você verá uma tela explicando o porquê.

### 3. Linha Duplicada Corrigida
Removido código PHP duplicado que estava quebrando a exibição.

---

## 📋 Como Testar (PASSO A PASSO)

### TESTE 1: Ativar Debug Completo

1. Acesse: `lacres_novo.php`
2. Selecione algumas datas para Poupa Tempo (exemplo: 20/01/2026, 21/01/2026)
3. Clique em "Gerar Ofício Poupa Tempo"
4. **NA NOVA ABA QUE ABRIR**, adicione na URL: `?debug_dados=1`
   
   Exemplo:
   ```
   http://seu-servidor/modelo_oficio_poupa_tempo.php?debug_dados=1
   ```

**✅ O que você deve ver:**

#### Bloco Vermelho (Dados Recebidos)
```
🔍 DEBUG v9.8.4 - DADOS RECEBIDOS
POST pt_datas: 2026-01-20,2026-01-21
GET pt_datas: NÃO DEFINIDO
datasStr final: 2026-01-20,2026-01-21

Todo POST:
Array (
    [pt_datas] => 2026-01-20,2026-01-21
)
```

#### Bloco Azul (Resultado da Busca)
```
🔍 DEBUG v9.8.4 - RESULTADO DA BUSCA
datasNorm (datas normalizadas): 2026-01-20, 2026-01-21
Total de páginas (postos): 3
temDados: SIM

Página #0: Posto 001 - CURITIBA CENTRO
  Total lotes: 2
  Qtd total: 1500

Página #1: Posto 002 - LONDRINA
  Total lotes: 1
  Qtd total: 450
```

---

### TESTE 2: Cenário SEM Dados

1. Selecione datas que **NÃO TÊM** produção (exemplo: 01/01/2026)
2. Clique em "Gerar Ofício Poupa Tempo"

**✅ O que você deve ver:**

Tela amarela com:
```
⚠️ Nenhum Ofício para Exibir

Não foram encontrados dados para gerar o ofício Poupa Tempo.

Possíveis causas:
• As datas selecionadas não têm produção cadastrada no sistema
• Nenhum posto Poupa Tempo tem lotes nas datas escolhidas
• Os postos não estão configurados com entrega "POUPA TEMPO"
• Problema na conexão com o banco de dados

[← Voltar e Selecionar Outras Datas]

Debug: Para mais detalhes, adicione ?debug_dados=1 na URL
```

---

### TESTE 3: Cenário COM Dados (Funcionamento Normal)

1. Selecione datas que TÊMM produção
2. Clique em "Gerar Ofício Poupa Tempo"
3. **NÃO adicione ?debug_dados=1**

**✅ O que você deve ver:**

Para cada posto, uma página com:

```
┌─────────────────────────────────────────────┐
│ CELEPAR               COSEP                 │
│                  Coordenação De Serviços    │
│                  Comprovante de Entrega     │
├─────────────────────────────────────────────┤
│ POUPATEMPO PARANA                           │
│ ENDERECO: [Campo editável]                  │
├─────────────────────────────────────────────┤
│ Poupatempo          │ Qtd CIN's │ Lacre    │
├─────────────────────────────────────────────┤
│ 001 - POSTO TESTE   │   7.822   │  [campo] │
└─────────────────────────────────────────────┘

📦 Lotes para Despacho (marque os lotes a enviar):
┌────────────────────────────────────────────┐
│ ☑  │  Lote       │  Quantidade            │
├────────────────────────────────────────────┤
│ ☑  │  LOTE_001   │  1.234                 │
│ ☑  │  LOTE_002   │  5.678                 │
│ ☑  │  LOTE_003   │  910                   │
├────────────────────────────────────────────┤
│    │  TOTAL (lotes marcados):  │  7.822   │
└────────────────────────────────────────────┘
```

---

## 🐛 Diagnóstico de Problemas

### Problema A: Debug mostra "POST pt_datas: NÃO DEFINIDO"

**Causa:** As datas não estão sendo enviadas do lacres_novo.php

**Solução:**
1. Verifique se você selecionou datas ANTES de clicar no botão
2. Inspecione o elemento `<input name="pt_datas">` no form
3. Verifique se o valor está preenchido

**Como verificar:**
```javascript
// No console do navegador (F12):
document.querySelector('[name="pt_datas"]').value
```

---

### Problema B: Debug mostra "datasStr final: VAZIO!"

**Causa:** As datas não estão chegando no POST

**Solução:**
1. Verifique se o formulário tem `method="post"`
2. Verifique se o input `pt_datas` está DENTRO do `<form>`
3. Tente usar GET ao invés de POST (teste):
   ```
   modelo_oficio_poupa_tempo.php?pt_datas=2026-01-20,2026-01-21
   ```

---

### Problema C: Debug mostra "Total de páginas: 0" mas datas estão OK

**Causa:** Query SQL não está retornando resultados

**Possíveis razões:**
1. **Datas não têm produção no banco**
   ```sql
   SELECT COUNT(*) FROM ciPostosCsv 
   WHERE DATE(dataCarga) IN ('2026-01-20', '2026-01-21');
   ```
   Se retornar 0: não há produção nessas datas

2. **Postos não configurados como Poupa Tempo**
   ```sql
   SELECT posto, entrega FROM ciRegionais 
   WHERE REPLACE(LOWER(entrega),' ','') LIKE 'poupa%tempo';
   ```
   Verifique se os postos estão na lista

3. **JOIN falhando**
   ```sql
   SELECT LPAD(c.posto,3,'0'), r.entrega
   FROM ciPostosCsv c
   LEFT JOIN ciRegionais r ON LPAD(r.posto,3,'0') = LPAD(c.posto,3,'0')
   WHERE DATE(c.dataCarga) = '2026-01-20'
   LIMIT 10;
   ```
   Veja se o JOIN está funcionando

---

### Problema D: Aparece mensagem "Nenhum Ofício para Exibir"

**Diagnóstico:**
1. Adicione `?debug_dados=1` na URL
2. Leia o bloco azul para ver exatamente onde está o problema
3. Siga as instruções da mensagem de erro

**Se o debug mostrar SQL vazio:**
- Problema: Query não executou
- Verifique conexão com o banco

**Se o debug mostrar 0 páginas:**
- Problema: Query retornou vazio
- Verifique se as datas têm produção

---

## 📊 Matriz de Diagnóstico

| Sintoma | Causa Provável | Solução |
|---------|----------------|---------|
| Tela branca/erro PHP | Erro de sintaxe | Verifique log PHP |
| Mensagem amarela "Nenhum Ofício" | Sem dados no banco | Selecione outras datas |
| Debug: POST pt_datas vazio | Form não enviou dados | Verifique seleção de datas |
| Debug: 0 páginas mas POST OK | Query SQL vazia | Verifique banco de dados |
| Lotes não aparecem | Array vazio | Verifique se lotes existem |

---

## ✅ Checklist de Validação

Após aplicar v9.8.4, valide:

- [ ] Debug com `?debug_dados=1` mostra dados recebidos
- [ ] Debug mostra resultado da busca SQL
- [ ] Mensagem de erro aparece se não houver dados
- [ ] Botão "Voltar" funciona
- [ ] Com dados, lotes aparecem corretamente
- [ ] Checkboxes funcionam normalmente
- [ ] Impressão oculta checkboxes

---

## 🎯 Próximos Passos

### Se NADA aparecer (nem debug, nem mensagem):
1. Verifique erro PHP no log:
   ```bash
   tail -f /var/log/apache2/error.log
   ```

2. Verifique se arquivo existe:
   ```bash
   ls -la modelo_oficio_poupa_tempo.php
   ```

3. Verifique permissões:
   ```bash
   chmod 644 modelo_oficio_poupa_tempo.php
   ```

### Se debug aparecer mas sem dados:
1. Execute a query SQL manualmente no banco
2. Verifique se `ciPostosCsv` tem dados nas datas
3. Verifique se `ciRegionais` tem entrega = "POUPA TEMPO"

### Se aparecer mas sem lotes:
1. Adicione `?debug_lotes=1` também
2. Verifique estrutura do array `$lotes`
3. Confirme que a query busca lotes individuais

---

## 📝 Relatório de Teste

Preencha após testar:

**Data do teste:** __/__/____  
**Testado por:** ___________

**Resultado TESTE 1 (Debug):**
- [ ] Debug vermelho apareceu
- [ ] Debug azul apareceu
- [ ] Dados estão corretos

**Resultado TESTE 2 (Sem Dados):**
- [ ] Mensagem amarela apareceu
- [ ] Botão voltar funciona
- [ ] Texto explicativo claro

**Resultado TESTE 3 (Com Dados):**
- [ ] Ofícios apareceram
- [ ] Lotes discriminados
- [ ] Checkboxes funcionam
- [ ] Total recalcula

**Status Final:**
- [ ] ✅ APROVADO - Tudo funcionando
- [ ] ⚠️ PARCIAL - Funciona mas com ressalvas
- [ ] ❌ REPROVADO - Não funciona

**Observações:**
_______________________________________________
_______________________________________________

---

**Versão:** 9.8.4  
**Data:** 26/01/2026  
**Prioridade:** 🔴 CRÍTICA - Diagnóstico de problema bloqueante
