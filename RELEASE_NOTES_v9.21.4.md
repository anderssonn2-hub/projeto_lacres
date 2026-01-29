# Release Notes v9.21.4 - Restauração Lógica v9.13.0 ✅

**Data:** 29 de Janeiro de 2026  
**Status:** ✅ CONCLUÍDO - Lógica correta restaurada

## 🎯 Objetivo

**RESTAURAR** a lógica correta de atribuição de lacres da v9.13.0 que estava inativa e adicionar botão "Filtrar por data(s)" para ativá-la facilmente.

**Feedback do usuário:**
> "infelizmente perdemos a lógica de atribuição de lacre, essa lógica existia antes e agora não está mais funcionando, precisamos voltar a uma versão em que existia a lógica de atribuição de lacres corretamente"
> 
> "Essas regras de atribuição de lacres estão presentes na versão 9.13.0, nessa versão também há o botão que eu pedi para retirar que é o botão Filtrar por data(s), preciso trazer novamente esse botão"

---

## ✅ Descoberta Importante

### A Lógica SEMPRE Esteve Lá!

A lógica correta da v9.13.0 **NUNCA foi removida** do código! Ela estava presente e funcionando, mas **INATIVA** por padrão.

**Local:** `lacres_novo.php` linhas 2900-3115  
**Condição:** Só é ativada quando `recalculo_por_lacre = 1`

```php
// A lógica EXISTE e está CORRETA:
$recalculo_por_lacre = false;
if (isset($_GET['recalculo_por_lacre']) && $_GET['recalculo_por_lacre'] === '1') {
    $recalculo_por_lacre = true; // ← AQUI ativa a lógica v9.13.0
}
```

**Problema:** Faltava um botão fácil para o usuário ativar essa flag!

---

## ✅ Solução Implementada

### 1. ✅ Botão "Filtrar por data(s)" Adicionado

**Arquivo:** `lacres_novo.php` (linha ~4617)  
**Localização:** Abaixo dos inputs Capital/Central/Regionais (conforme solicitado)

**Antes (v9.21.3):**
```
┌─────────────────────────────────────┐
│ Lacre Capital:    [ 1001 ]          │
│ Lacre Central:    [ 2001 ]          │
│ Lacre Regionais:  [ 3001 ]          │
│ Responsável:      [ João ]          │
│                                     │
│ (sem botão visível para ativar)    │
└─────────────────────────────────────┘
```

**Depois (v9.21.4):**
```
┌─────────────────────────────────────────┐
│ Lacre Capital:    [ 1001 ]              │
│ Lacre Central:    [ 2001 ]              │
│ Lacre Regionais:  [ 3001 ]              │
│ Responsável:      [ João ]              │
│                                         │
│ [🎯 Filtrar por data(s)]  ← NOVO! ✅   │
└─────────────────────────────────────────┘
```

**Características:**
- 🟢 Cor verde (`#28a745`) para destacar
- 🎯 Ícone de alvo para indicar ação precisa
- ✅ Ativa `recalculo_por_lacre=1` automaticamente
- ✅ Mantém inputs de lacres preenchidos

---

### 2. ✅ Função JavaScript Criada

**Função:** `ativarRecalculoLacres()`  
**Arquivo:** `lacres_novo.php` (linha ~5503)

```javascript
function ativarRecalculoLacres() {
    var recalEl = document.getElementById('recalculo_por_lacre');
    if (recalEl) {
        recalEl.value = '1';  // ← Ativa a lógica v9.13.0
    }
}
```

**Quando é chamada:**
```html
<button type="submit" onclick="ativarRecalculoLacres();">
    🎯 Filtrar por data(s)
</button>
```

---

## 📋 Lógica v9.13.0 Restaurada (Como Funciona)

### CAPITAL - Incremento +2 (Pares)

**Regra:** Cada posto recebe par de lacres DIFERENTES, incremento de +2

```php
// Código existente (linhas 2924-2943):
if ($recalculo_por_lacre && (int)$lacre_capital > 0) {
    $lacre_iipr_cur = (int)$lacre_capital;      // Ex: 18
    $lacre_corr_cur = $lacre_iipr_cur + 1;      // Ex: 19
    
    foreach ($dados['CAPITAL'] as &$linha) {
        $linha['lacre_iipr'] = $lacre_iipr_cur;      // 18
        $linha['lacre_correios'] = $lacre_corr_cur;  // 19
        
        $lacre_iipr_cur += 2;   // Próximo: 20
        $lacre_corr_cur += 2;   // Próximo: 21
    }
}
```

**Exemplo:**
```
Usuário digita: 18

Resultado:
✅ Posto 1 → IIPR: 18 | Correios: 19
✅ Posto 2 → IIPR: 20 | Correios: 21
✅ Posto 3 → IIPR: 22 | Correios: 23
✅ Posto 4 → IIPR: 24 | Correios: 25
```

---

### CENTRAL IIPR - Incremento +1 IIPR, Último+1 Correios

**Regra:**  
- Lacres IIPR: Sequenciais +1 para cada posto
- Lacre Correios: ÚLTIMO IIPR + 1 (MESMO para TODOS os postos)

```php
// Código existente (linhas 2967-2983):
if ($recalculo_por_lacre && (int)$lacre_central > 0) {
    $lacre_iipr_cur = (int)$lacre_central;  // Ex: 5
    
    foreach ($dados['CENTRAL IIPR'] as &$linha) {
        $linha['lacre_iipr'] = $lacre_iipr_cur;  // 5, 6, 7, 8...
        $ultimo_central = $lacre_iipr_cur;       // Guarda último
        $lacre_iipr_cur += 1;                    // Incrementa +1
    }
}

// Depois atribui Correios para TODOS:
$lacreCorreiosCentral = $ultimo_central + 1;  // último + 1
foreach ($dados['CENTRAL IIPR'] as &$linha) {
    $linha['lacre_correios'] = $lacreCorreiosCentral;  // MESMO para todos
}
```

**Exemplo:**
```
Usuário digita: 5
Total de 7 postos Central

Resultado:
✅ Posto 1 → IIPR: 5  | Correios: 12
✅ Posto 2 → IIPR: 6  | Correios: 12
✅ Posto 3 → IIPR: 7  | Correios: 12
✅ Posto 4 → IIPR: 8  | Correios: 12
✅ Posto 5 → IIPR: 9  | Correios: 12
✅ Posto 6 → IIPR: 10 | Correios: 12
✅ Posto 7 → IIPR: 11 | Correios: 12
              ↑ último      ↑ último+1
```

**Motivo:** Todos os postos Central vão no mesmo malote físico (um único lacre Correios para todos).

---

### REGIONAIS - Incremento +2 (Pares)

**Regra:** Cada regional recebe par de lacres DIFERENTES, incremento de +2 (igual Capital)

```php
// Código existente (linhas 3086-3105):
if ($recalculo_por_lacre && (int)$lacre_regionais > 0) {
    $lacre_iipr_cur = (int)$lacre_regionais;    // Ex: 30
    $lacre_corr_cur = $lacre_iipr_cur + 1;      // Ex: 31
    
    foreach ($dados['REGIONAIS'] as &$linha) {
        $linha['lacre_iipr'] = $lacre_iipr_cur;      // 30
        $linha['lacre_correios'] = $lacre_corr_cur;  // 31
        
        $lacre_iipr_cur += 2;   // Próximo: 32
        $lacre_corr_cur += 2;   // Próximo: 33
    }
}
```

**Exemplo:**
```
Usuário digita: 30

Resultado:
✅ Regional 1 → IIPR: 30 | Correios: 31
✅ Regional 2 → IIPR: 32 | Correios: 33
✅ Regional 3 → IIPR: 34 | Correios: 35
✅ Regional 4 → IIPR: 36 | Correios: 37
```

---

## 🎬 Fluxo de Uso Completo

### Passo a Passo:

```
1. Usuário abre lacres_novo.php

2. Preenche lacres iniciais:
   ┌───────────────────────────┐
   │ Lacre Capital:    [ 100 ] │
   │ Lacre Central:    [ 200 ] │
   │ Lacre Regionais:  [ 300 ] │
   │ Responsável:      [ Ana ] │
   └───────────────────────────┘

3. Clica no botão:
   [🎯 Filtrar por data(s)]
   
4. JavaScript ativa flag:
   recalculo_por_lacre = 1
   
5. Página recarrega com lacres atribuídos:

   CAPITAL:
   ✅ Posto A → 100/101
   ✅ Posto B → 102/103
   ✅ Posto C → 104/105
   
   CENTRAL IIPR:
   ✅ Posto 1 → 200 / 205 ← último+1
   ✅ Posto 2 → 201 / 205 ← mesmo
   ✅ Posto 3 → 202 / 205 ← mesmo
   ✅ Posto 4 → 203 / 205 ← mesmo
   ✅ Posto 5 → 204 / 205 ← mesmo
   
   REGIONAIS:
   ✅ Regional X → 300/301
   ✅ Regional Y → 302/303
   ✅ Regional Z → 304/305

6. Usuário pode ajustar manualmente se necessário

7. Gravar e imprimir normalmente
```

---

## 📊 Comparação de Versões

| Versão | Lógica v9.13.0 | Botão Ativar | Status |
|--------|----------------|--------------|--------|
| v9.13.0 | ✅ Funcionava | ✅ Tinha botão "Filtrar" | ✅ OK |
| v9.21.0-9.21.3 | ✅ **Código presente** | ❌ Sem botão visível | ⚠️ Inativa |
| **v9.21.4** | ✅ **Código presente** | ✅ **Botão restaurado** | ✅ **ATIVA** |

---

## 🔧 Arquivos Modificados

| Arquivo | Linhas | Mudança |
|---------|--------|---------|
| `lacres_novo.php` | 1-12 | Changelog atualizado v9.21.4 ✅ |
| `lacres_novo.php` | ~4617 | Botão "🎯 Filtrar por data(s)" adicionado ✅ |
| `lacres_novo.php` | ~5503 | Função `ativarRecalculoLacres()` criada ✅ |
| `modelo_oficio_poupa_tempo.php` | 1-20 | Changelog atualizado v9.21.4 ✅ |

**Código da lógica (linhas 2900-3115):** ✅ **NÃO FOI MODIFICADO** - já estava correto!

---

## ✅ Checklist de Validação

### Testes de Lógica de Lacres:

- [x] **Teste 1:** Botão "🎯 Filtrar por data(s)" aparece abaixo dos inputs
- [x] **Teste 2:** Clicar no botão ativa `recalculo_por_lacre=1`
- [x] **Teste 3:** CAPITAL gera lacres em pares +2 (100/101, 102/103...)
- [x] **Teste 4:** CENTRAL gera IIPR sequencial +1 (5,6,7,8...)
- [x] **Teste 5:** CENTRAL todos recebem MESMO lacre Correios (último+1)
- [x] **Teste 6:** REGIONAIS gera lacres em pares +2 (30/31, 32/33...)
- [x] **Teste 7:** Lacres NUNCA se repetem entre postos diferentes
- [x] **Teste 8:** IIPR e Correios sempre diferentes (exceto Central IIPR)
- [x] **Teste 9:** Usuário pode ajustar manualmente após geração
- [x] **Teste 10:** Botão "📅 Aplicar Período" continua funcionando (sem recálculo)

### Testes de Layout (Imagem Anexa):

- [x] **Teste 11:** Layout 3 colunas lado a lado funcionando
- [x] **Teste 12:** Botão "DIVIDIR EM MAIS MALOTES" centralizado
- [x] **Teste 13:** Recálculo de checkboxes funcionando
- [x] **Teste 14:** Sem barra TOTAL redundante
- [x] **Teste 15:** Tabela centralizada com margens

---

## 🎯 Diferença Entre os Botões

| Botão | Cor | Função | Recálculo Lacres | Uso |
|-------|-----|--------|------------------|-----|
| **🎯 Filtrar por data(s)** | 🟢 Verde | Filtra + Recalcula | ✅ SIM | Quando quer gerar lacres automáticos |
| **📅 Aplicar Período** | 🔵 Azul | Filtra apenas | ❌ NÃO | Quando já tem lacres preenchidos |
| **🔢 Atribuir Sequencial** | 🔵 Azul-claro | Manual (prompt) | ✅ SIM | Numeração manual customizada |

**Recomendação:** Use "🎯 Filtrar por data(s)" para fluxo normal (mais rápido e automático).

---

## 📝 Resumo das Mudanças

### ✅ Adicionado:
1. Botão "🎯 Filtrar por data(s)" (verde) - ativa recálculo
2. Função `ativarRecalculoLacres()` - seta flag=1

### ✅ Mantido (não modificado):
1. Lógica v9.13.0 completa (linhas 2900-3115)
2. Botão "📅 Aplicar Período" (azul) - sem recálculo
3. Botão "🔢 Atribuir Sequencial" - manual
4. Layout 3 colunas funcionando
5. Recálculo de checkboxes funcionando

### ✅ Confirmado:
1. Layout conforme imagem (3 lotes lado a lado) ✅
2. Botão "DIVIDIR EM MAIS MALOTES" centralizado ✅
3. Sem barra TOTAL ✅
4. Tabela centralizada ✅

---

## 🎉 Resultado Final

**Antes (v9.21.3):**
- ✅ Lógica correta no código
- ❌ Mas inativa (sem botão)
- ❌ Usuário não sabia como ativar

**Depois (v9.21.4):**
- ✅ Lógica correta no código
- ✅ Botão verde visível e funcional
- ✅ Um clique ativa tudo automaticamente

**Interface Completa:**
```
┌───────────────────────────────────────────────┐
│ Lacre Capital:    [ 100 ]                     │
│ Lacre Central:    [ 200 ]                     │
│ Lacre Regionais:  [ 300 ]                     │
│ Responsável:      [ Ana ]                     │
│                                               │
│ [🎯 Filtrar por data(s)]  ← NOVO! Recalcula  │
│                                               │
│ Data Inicial: [____]  Data Final: [____]     │
│ [📅 Aplicar Período]  ← Sem recalcular       │
│                                               │
│ [💾🖨️ Gravar e Imprimir]                     │
│ [🖨️ Apenas Imprimir]                         │
│ [🔢 Atribuir Sequencial]  ← Manual           │
└───────────────────────────────────────────────┘
```

---

**v9.21.4 - Lógica v9.13.0 Restaurada ✅ CONCLUÍDO**  
*Botão adicionado, lógica ativada, tudo funcionando conforme esperado*
