# 🧪 TESTE v9.8.3 - Exibição de Lotes no Ofício Poupa Tempo

## Data: 26/01/2026

## 🎯 Objetivo
Validar que os lotes individuais estão sendo exibidos corretamente no ofício Poupa Tempo com checkboxes funcionais.

---

## 📋 Checklist de Teste

### 1️⃣ Geração do Ofício com Debug
```
1. Acesse: lacres_novo.php
2. Selecione datas para Poupa Tempo
3. Clique em "Gerar Ofício PT"
4. Na URL adicione: ?debug_lotes=1
   Exemplo: modelo_oficio_poupa_tempo.php?debug_lotes=1
```

**✅ Resultado Esperado:**
- Deve aparecer um bloco amarelo no topo com:
  ```
  DEBUG LOTES v9.8.3
  Total de postos: X
  
  Posto #0: 001 - NOME DO POSTO
    Total lotes: Y
    Qtd total: Z
    Lote [0]: LOTE_XXX = N CINs
    Lote [1]: LOTE_YYY = M CINs
  ```

**❌ Se não aparecer:** Os lotes não estão sendo buscados do banco. Verifique:
- As datas selecionadas têm produção?
- A query SQL está retornando dados?

---

### 2️⃣ Visualização da Tabela de Lotes

**✅ Resultado Esperado:**
Para cada posto no ofício, deve aparecer:

```
┌────────────────────────────────────────────────────┐
│ 📦 Lotes para Despacho (marque os lotes a enviar): │
├────────────────────────────────────────────────────┤
│ ☑  |  Lote           |  Quantidade                 │
├────────────────────────────────────────────────────┤
│ ☑  |  LOTE_001       |  1.234                      │
│ ☑  |  LOTE_002       |  5.678                      │
│ ☑  |  LOTE_003       |  910                        │
├────────────────────────────────────────────────────┤
│    |  TOTAL (lotes marcados):  |  7.822            │
└────────────────────────────────────────────────────┘
```

**✅ Características:**
- Fundo cinza claro (#f9f9f9)
- Borda sólida
- Título em negrito com emoji 📦
- Todos os checkboxes marcados por padrão
- Total exibido no rodapé

**❌ Se não aparecer:**
- Aparece mensagem: "⚠️ Aviso: Nenhum lote encontrado para este posto"?
  - Se SIM: As datas não têm lotes para este posto
  - Se NÃO: A tabela está sendo ocultada por CSS incorreto

---

### 3️⃣ Funcionamento dos Checkboxes

**Teste 1: Desmarcar um lote**
1. Desmarque o checkbox de um lote
2. Observe o total no rodapé da tabela
3. Observe o total no campo "Quantidade de CIN's" (tabela principal)

**✅ Resultado Esperado:**
- Total diminui imediatamente
- Ambos os totais (rodapé e campo principal) são atualizados
- Formato numérico mantém separador de milhares (ex: 1.234)

**Teste 2: Desmarcar todos**
1. Clique no checkbox do cabeçalho (☑ no topo)
2. Todos os lotes devem ser desmarcados
3. Total deve ficar em 0

**Teste 3: Remarcar todos**
1. Clique novamente no checkbox do cabeçalho
2. Todos os lotes devem ser marcados
3. Total deve voltar ao valor original

---

### 4️⃣ Impressão (Ctrl+P)

**Teste:**
1. Desmarque alguns lotes
2. Pressione Ctrl+P (ou clique em "Imprimir")
3. Observe a pré-visualização de impressão

**✅ Resultado Esperado:**
- Checkboxes NÃO aparecem (coluna oculta)
- Título "Lotes para Despacho" NÃO aparece
- Lotes DESMARCADOS não aparecem (linhas ocultas)
- Lotes MARCADOS aparecem normalmente
- Tabela tem borda fina (#ccc)
- Fundo branco/transparente

**❌ Problemas comuns:**
- Se checkboxes aparecem: CSS de impressão não está funcionando
- Se lotes desmarcados aparecem: atributo `data-checked` não está sendo atualizado
- Se a tabela toda some: CSS está ocultando demais

---

### 5️⃣ Gravação no Banco

**Teste:**
1. Desmarque alguns lotes
2. Clique em "Gravar" (escolha "Sobrescrever" ou "Criar Novo")
3. Volte e edite o ofício

**✅ Resultado Esperado:**
- Apenas os lotes marcados são salvos
- Quantidade total salva corresponde à soma dos lotes marcados

**🔍 Como verificar:**
```sql
SELECT posto, lote, quantidade 
FROM ciDespachoLotes 
WHERE id_despacho = [ID_DO_OFICIO]
ORDER BY posto, lote;
```

---

## 🐛 Troubleshooting

### Problema: Nenhum lote aparece

**Possíveis causas:**
1. Array `$lotes_array` está vazio
   - Solução: Adicione `?debug_lotes=1` na URL e verifique a estrutura

2. Query SQL não retorna lotes individuais
   - Solução: Verifique se a query usa `c.lote AS lote` (não `GROUP BY`)

3. Loop PHP não está executando
   - Solução: Verifique se há `<?php foreach ($lotes_array as $lote_info): ?>`

### Problema: Checkboxes não recalculam total

**Solução:**
1. Abra Console do navegador (F12)
2. Procure erros JavaScript
3. Verifique se função `recalcularTotal()` existe no código

### Problema: Na impressão aparecem checkboxes

**Solução:**
Verifique se o CSS de impressão tem:
```css
@media print {
    .titulo-controle,
    .checkbox-lote,
    .marcar-todos,
    .col-checkbox{
        display:none !important;
    }
}
```

---

## ✅ Critérios de Aceitação

A versão 9.8.3 está APROVADA quando:

- [ ] Debug mostra estrutura de lotes correta
- [ ] Tabela de lotes aparece para todos os postos
- [ ] Checkboxes funcionam e recalculam total
- [ ] Impressão oculta checkboxes e lotes desmarcados
- [ ] Gravação salva apenas lotes marcados
- [ ] Não há erros no console JavaScript
- [ ] Não há erros PHP no log

---

## 📝 Relatório de Bugs

Se encontrar problemas, documente:

**Bug #___**
- Descrição:
- Passos para reproduzir:
- Resultado esperado:
- Resultado obtido:
- Navegador/versão:
- Screenshot (se aplicável):

---

## 🎉 Conclusão

Após validação completa, atualize este documento com:
- [ ] Data do teste: __/__/____
- [ ] Testado por: ___________
- [ ] Status: ☑ APROVADO / ☐ REPROVADO
- [ ] Observações: __________
