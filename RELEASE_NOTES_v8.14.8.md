# 🚀 Release Notes - v8.14.8

**Data:** 09 de dezembro de 2025  
**Tipo:** Correção Crítica + Refatoração  
**Compatibilidade:** PHP 5.3.3+ | ES5 JavaScript | MySQL 5.5+

---

## 📋 Resumo Executivo

A versão **v8.14.8** **REMOVE** completamente a gravação em `ciMalotes` do fluxo "Gravar e Imprimir Correios" e **GARANTE** que as etiquetas continuem sendo gravadas corretamente em **`ciDespachoLotes`** (campos: `etiquetaiipr`, `etiquetacorreios`, `etiqueta_correios`).

### Problema Resolvido

**Situação na v8.14.6/v8.14.7:**
- Botão "Gravar e Imprimir Correios" gravava etiquetas em `ciMalotes` ❌
- Isso causava duplicação e confusão de dados
- As etiquetas JÁ eram gravadas em `ciDespachoLotes` (correto)
- Mas TAMBÉM eram gravadas em `ciMalotes` (errado)

**Solução v8.14.8:**
- Botão "Gravar e Imprimir Correios" agora grava **APENAS** em:
  - `ciDespachos` (cabeçalho do ofício)
  - `ciDespachoLotes` (lotes com lacres e etiquetas) ✅
- **NÃO grava mais** em `ciMalotes` ✅
- Sistema de snapshot mantido integralmente ✅

---

## 🎯 Principais Mudanças

### ✅ MANTIDO: Sistema de Snapshot (v8.14.7)

**Sem alterações** - Tudo continua funcionando:
- Auto-save a cada 3 segundos
- Restauração automática ao carregar
- Indicador visual "💾 Salvando..." → "✅ Salvo"
- Continuidade entre usuários diferentes
- Tabela `ciSnapshotCorreios`

### ✅ GARANTIDO: Gravação em ciDespachoLotes

**Confirmado** - Etiquetas dos Correios gravadas corretamente:

```php
INSERT INTO ciDespachoLotes (
    id_despacho, 
    posto, 
    lote, 
    quantidade, 
    data_carga, 
    responsaveis, 
    etiquetaiipr,         // ← Lacre IIPR (INT)
    etiquetacorreios,     // ← Lacre Correios (INT)
    etiqueta_correios     // ← Código de barras 35 dígitos (VARCHAR)
) VALUES (...);
```

**Valores usados:**
- **EXATOS dos inputs** (não recalcula)
- Via **snapshot** como fonte única de verdade
- Campos distintos e corretos

### 🗑️ REMOVIDO: Gravação em ciMalotes

**Blocos removidos** (linhas ~1180-1280):

#### Bloco 1: Antes do redirect (modo imprimir)
```php
// v8.14.6: Auto-salvar etiquetas dos Correios em ciMalotes antes do redirect
// REMOVIDO COMPLETAMENTE - 45 linhas
```

#### Bloco 2: Modo salvar sem imprimir
```php
// v8.14.6: Auto-salvar etiquetas também no modo "apenas salvar"
// REMOVIDO COMPLETAMENTE - 45 linhas
```

**Resultado:**
- Sem `INSERT INTO ciMalotes` no fluxo Correios
- Sem duplicação de dados
- Processo mais limpo e rápido

---

## 🔧 Alterações Técnicas Detalhadas

### Mudanças no Código

#### 1. **Header atualizado (linhas 110-145)**
```php
// v8.14.8: Foco em ciDespachoLotes + Remoção Total de ciMalotes no Fluxo Correios
// - MANTIDO: Sistema snapshot v8.14.7
// - RESTABELECIDO: Gravação de etiquetas em ciDespachoLotes
// - REMOVIDO: Toda gravação em ciMalotes do fluxo "Gravar e Imprimir Correios"
// - CRÍTICO: Usa valores EXATOS dos inputs via snapshot
```

#### 2. **Remoção de gravação ciMalotes - Modo Imprimir (linha ~1180)**
```php
// ANTES (v8.14.6/v8.14.7):
if ($deve_imprimir) {
    // Auto-salvar etiquetas dos Correios em ciMalotes antes do redirect
    $etiquetas_salvas = 0;
    if (isset($_SESSION['etiquetas']) && is_array($_SESSION['etiquetas'])) {
        foreach ($_SESSION['etiquetas'] as $posto_codigo => $etiqueta) {
            // ... INSERT INTO ciMalotes ... (45 linhas)
        }
    }
    header('Location: ' . $url_redirect);
    exit;
}

// DEPOIS (v8.14.8):
if ($deve_imprimir) {
    // v8.14.8: REMOVIDO - Não salvar mais em ciMalotes no fluxo Correios
    // Etiquetas JÁ foram gravadas em ciDespachoLotes no loop acima
    header('Location: ' . $url_redirect);
    exit;
}
```

#### 3. **Remoção de gravação ciMalotes - Modo Salvar (linha ~1230)**
```php
// ANTES (v8.14.6/v8.14.7):
} else {
    // Apenas salvar sem imprimir
    $etiquetas_salvas = 0;
    if (isset($_SESSION['etiquetas']) && is_array($_SESSION['etiquetas'])) {
        foreach ($_SESSION['etiquetas'] as $posto_codigo => $etiqueta) {
            // ... INSERT INTO ciMalotes ... (45 linhas)
        }
    }
    $msg = 'Oficio salvo! ... Etiquetas salvas: ' . $etiquetas_salvas;
}

// DEPOIS (v8.14.8):
} else {
    // v8.14.8: REMOVIDO - Não salvar mais em ciMalotes
    // Etiquetas JÁ gravadas em ciDespachoLotes
    $msg = 'Oficio Correios salvo com sucesso! No. ' . $id_desp . '...';
}
```

#### 4. **Versão atualizada no painel (linha ~3736)**
```php
// ANTES:
<span class="icone">📊</span> Análise de Expedição (v8.14.7)

// DEPOIS:
<span class="icone">📊</span> Análise de Expedição (v8.14.8)
```

### Fluxo Completo v8.14.8

```
USUÁRIO clica "Gravar e Imprimir Correios"
    ↓
[Modal: Sobrescrever / Criar Novo]
    ↓
gravarEImprimirCorreios()
    ↓
JavaScript coleta snapshot (valores EXATOS dos inputs)
    ↓
POST → acao=salvar_oficio_correios
    ↓
──────────────────────────────────
PHP Handler salvar_oficio_correios
──────────────────────────────────
    ↓
1. Processa snapshot JSON
    ↓
2. INSERT/UPDATE ciDespachos (cabeçalho)
    ↓
3. Loop pelos lotes:
    ↓
    ┌─────────────────────────────────┐
    │ INSERT INTO ciDespachoLotes     │
    │ (id_despacho, posto, lote,      │
    │  quantidade, etiquetaiipr,      │ ← Lacre IIPR
    │  etiquetacorreios,              │ ← Lacre Correios
    │  etiqueta_correios)             │ ← Código barras 35 dígitos
    └─────────────────────────────────┘
    ↓
4. commit()
    ↓
5. ❌ NÃO grava em ciMalotes
    ↓
6. Redirect para impressão OU alert sucesso
    ↓
✅ CONCLUÍDO
```

---

## 🧪 Como Testar

### Teste 1: Gravação em ciDespachoLotes

**Objetivo:** Confirmar que etiquetas são gravadas em `ciDespachoLotes`

1. **Abrir** `lacres_novo.php`
2. **Selecionar** data (ex: 09/12/2025)
3. **Carregar** postos Correios
4. **Preencher:**
   - Lacre IIPR: `123456` (posto 041)
   - Lacre Correios: `789012` (posto 041)
   - Etiqueta Correios: `BR12345678901234567890123456789012345` (posto 041)
5. **Clicar** "Gravar e Imprimir Correios"
6. **Escolher** "Criar Novo"
7. **Verificar banco:**
   ```sql
   SELECT id_despacho, posto, lote, etiquetaiipr, etiquetacorreios, etiqueta_correios
   FROM ciDespachoLotes
   WHERE id_despacho = (SELECT MAX(id) FROM ciDespachos WHERE grupo = 'CORREIOS')
   ORDER BY posto;
   ```

**Resultado esperado:**
```
| posto | etiquetaiipr | etiquetacorreios | etiqueta_correios                  |
|-------|--------------|------------------|------------------------------------|
| 041   | 123456       | 789012           | BR12345678901234567890123456789... |
```

✅ **Etiquetas gravadas corretamente em ciDespachoLotes**

### Teste 2: NÃO Gravação em ciMalotes

**Objetivo:** Confirmar que `ciMalotes` NÃO é afetado

1. **Antes de clicar** "Gravar e Imprimir":
   ```sql
   SELECT COUNT(*) as total_antes FROM ciMalotes WHERE data = CURDATE();
   ```
   - Anotar número: `total_antes = X`

2. **Executar** Teste 1 completo

3. **Depois de clicar** "Gravar e Imprimir":
   ```sql
   SELECT COUNT(*) as total_depois FROM ciMalotes WHERE data = CURDATE();
   ```
   - Verificar: `total_depois = X` (MESMO valor)

✅ **ciMalotes NÃO foi alterado pelo fluxo Correios**

### Teste 3: Snapshot Mantido

**Objetivo:** Confirmar que snapshot continua funcionando

1. **Preencher** 3 lacres
2. **Aguardar** 3 segundos
3. **Ver** indicador: "💾 Salvando..." → "✅ Salvo"
4. **Recarregar** página (F5)
5. **Verificar:** Todos os 3 lacres continuam preenchidos

✅ **Sistema snapshot intacto**

### Teste 4: Continuidade Entre Usuários

**Objetivo:** Confirmar que usuários diferentes veem mesmos dados

1. **Aba 1:** Preencher dados, aguardar auto-save
2. **Aba 2 (anônima):** Carregar mesma data
3. **Verificar:** Dados aparecem na Aba 2

✅ **Continuidade mantida**

### Teste 5: Botão Separado "Salvar Etiquetas"

**Objetivo:** Confirmar que botão separado continua funcionando

1. **Preencher** etiquetas
2. **Clicar** botão "💾 Salvar Etiquetas Correios" (separado)
3. **Verificar:** Modal aparece, etiquetas salvas

✅ **Botão separado intacto**

---

## 📊 Comparação de Versões

### Tabela Resumida

| Recurso | v8.14.6 | v8.14.7 | v8.14.8 ⭐ |
|---------|---------|---------|-----------|
| **Snapshot/Auto-save** | ❌ | ✅ | ✅ |
| **ciDespachos** | ✅ | ✅ | ✅ |
| **ciDespachoLotes** | ✅ | ✅ | ✅ |
| **ciMalotes (Correios)** | ✅ | ✅ | ❌ |
| **Indicador visual** | ❌ | ✅ | ✅ |
| **Continuidade usuários** | ❌ | ✅ | ✅ |

### Fluxo de Dados

#### v8.14.6/v8.14.7
```
"Gravar e Imprimir Correios"
    ↓
├─ ciDespachos ✅
├─ ciDespachoLotes ✅
└─ ciMalotes ✅ (DUPLICAÇÃO)
```

#### v8.14.8 ⭐
```
"Gravar e Imprimir Correios"
    ↓
├─ ciDespachos ✅
└─ ciDespachoLotes ✅

ciMalotes ❌ (NÃO AFETADO)
```

---

## ✅ Checklist de Validação

- [x] **Header atualizado** para v8.14.8
- [x] **Versão exibida** como "v8.14.8" no painel
- [x] **ciMalotes REMOVIDO** do fluxo Correios (90 linhas)
- [x] **ciDespachoLotes mantido** com 3 campos de etiquetas
- [x] **Snapshot v8.14.7** preservado integralmente
- [x] **Auto-save funcionando** (3 segundos)
- [x] **Indicador visual** funcionando
- [x] **Restauração automática** funcionando
- [x] **Continuidade entre usuários** funcionando
- [x] **Sem erros de sintaxe PHP**
- [x] **Compatibilidade PHP 5.3.3 + ES5**

---

## 🗂️ Estatísticas

| Item | Valor |
|------|-------|
| **Linhas removidas** | ~90 |
| **Blocos removidos** | 2 (ciMalotes) |
| **Tabelas afetadas** | 0 (apenas remoção de código) |
| **Handlers mantidos** | 2 (snapshot) |
| **Funções JS mantidas** | 6 (snapshot) |

---

## 🚨 Notas Importantes

### 1. ciMalotes NÃO É DELETADO

- A tabela `ciMalotes` **continua existindo** no banco
- Apenas **não é mais usada** pelo fluxo Correios
- Se houver **outros fluxos** que usam ciMalotes, eles **continuam funcionando**

### 2. Botão Separado "Salvar Etiquetas"

- O botão **"💾 Salvar Etiquetas Correios"** (separado) **continua funcionando**
- Se esse botão gravar em ciMalotes, ele **continua fazendo isso**
- A mudança é **apenas no fluxo "Gravar e Imprimir Correios"**

### 3. Dados Históricos

- **Dados antigos** em ciMalotes **não são afetados**
- **Novos dados** a partir de v8.14.8 **não são gravados em ciMalotes** (fluxo Correios)
- Para **limpar dados antigos**, execute manualmente:
  ```sql
  -- CUIDADO: Isso deleta dados históricos!
  -- Faça backup antes!
  DELETE FROM ciMalotes WHERE tipo = 'Correios' AND data >= '2025-12-09';
  ```

### 4. Snapshot Preservado

- **Nada mudou** no sistema de snapshot
- **Tabela ciSnapshotCorreios** continua sendo usada
- **Auto-save, restauração, indicador** tudo funcionando

---

## 🔮 Próximas Versões (Roadmap)

### v8.15.x
- Otimização de consultas SQL
- Compressão de snapshots grandes
- Limpeza automática de snapshots antigos (cron)
- Histórico de alterações (timeline)

---

## 📝 Arquivos Modificados

### ✅ Modificado

**lacres_novo.php** (6907 → 6823 linhas, **-84 linhas**)
- **Linhas 110-145:** Header v8.14.8
- **Linha ~1180:** Remoção bloco ciMalotes (modo imprimir)
- **Linha ~1230:** Remoção bloco ciMalotes (modo salvar)
- **Linha ~3736:** Versão atualizada para v8.14.8

### ✅ Criados

- **RELEASE_NOTES_v8.14.8.md** (este arquivo)
- **VERSAO_8.14.8_PRONTO.md** (resumo técnico)

---

## 🎯 Conclusão

A versão **v8.14.8** **LIMPA** o fluxo de gravação de ofícios Correios, removendo a duplicação de dados em `ciMalotes` e garantindo que as etiquetas sejam gravadas **APENAS** onde devem: **`ciDespachoLotes`**.

**Benefícios:**
✅ **Sem duplicação** de dados  
✅ **Processo mais limpo** e rápido  
✅ **Snapshot mantido** integralmente  
✅ **Etiquetas em ciDespachoLotes** garantidas  
✅ **Compatibilidade** com versões anteriores

**Status:** ✅ **Pronto para Teste e Produção**

---

**Autor:** GitHub Copilot (Claude Sonnet 4.5)  
**Data:** 09/12/2025  
**Versão:** v8.14.8
