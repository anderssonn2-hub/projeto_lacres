# Release Notes - v9.9.6
**Data:** 27/01/2026

## 🎯 Objetivo desta Versão
Correções críticas baseadas em testes reais do usuário:
1. Estrutura do código de barras estava incorreta
2. Linhas amarelas não apareciam na impressão mesmo marcadas
3. Layout precisa suportar 2 colunas para muitos lotes
4. Rodapé longe do final no PDF

---

## ✅ Correções Implementadas

### 1. Estrutura do Código de Barras CORRIGIDA ⚠️

**Problema:** Quantidade sendo extraída incorretamente.
- **Código testado:** `0075942402302300170`
- **Quantidade esperada:** 170
- **Quantidade extraída (v9.9.5):** 230 ❌

**Causa Raiz:**
```javascript
// ANTES (v9.9.5) - INCORRETO:
quantidadeExtraida = parseInt(codigoLido.substring(8, 12), 10);
// Extraía posições 8-11 = "0230" = 230 ❌
```

**Estrutura Real do Código (19 dígitos):**
```
Exemplo: 0075942402302300170
         ^^^^^^^^ ^^^^^^ ^^^^^
         │        │      └───── 5 dígitos: QUANTIDADE (00170 = 170)
         │        └──────────── 6 dígitos: OUTROS DADOS
         └───────────────────── 8 dígitos: LOTE

Posições:
- 0-7:   LOTE (8 dígitos)
- 8-13:  OUTROS (6 dígitos)
- 14-18: QUANTIDADE (5 dígitos) ← ÚLTIMOS 5 DÍGITOS
```

**Solução:**
```javascript
// DEPOIS (v9.9.6) - CORRETO:
quantidadeExtraida = parseInt(codigoLido.substring(14, 19), 10);
// Extrai últimos 5 dígitos = "00170" = 170 ✓
```

**Resultado:**
```
Código: 0075942402302300170
Lote:   00759424 (posições 0-7)
Qtd:    170 (posições 14-18) ✓
```

---

### 2. Linhas Amarelas na Impressão ✅

**Problema:** Linhas adicionadas (não cadastradas) não apareciam na impressão mesmo com checkbox marcado.

**Causa Raiz:**
```css
/* v9.9.5 - BLOQUEAVA SEMPRE */
.linha-lote.nao-encontrado{
    display:none !important;
}
```

**Solução:**
```css
/* v9.9.6 - CONDICIONAL BASEADO EM data-checked */
.linha-lote.nao-encontrado[data-checked="0"]{
    display:none !important;
}
.linha-lote.nao-encontrado[data-checked="1"]{
    display:table-row !important;
    background:transparent !important; /* Remove amarelo na impressão */
}
```

**JavaScript atualizado:**
```javascript
checkbox.onchange = function() { 
    // Atualiza data-checked para controlar visibilidade
    novaLinha.setAttribute('data-checked', this.checked ? '1' : '0');
    recalcularTotal(codigoPosto); 
};
```

**Resultado:**
- ✅ Tela: Linha amarela visível
- ✅ Checkbox desmarcado: NÃO imprime
- ✅ Checkbox marcado: IMPRIME (sem cor amarela)

---

### 3. Layout 2 Colunas (Planejado para v9.10.0)

**Requisito:** Quando muitos lotes (ex: >15), dividir em 2 colunas lado a lado.

**Análise Técnica:**
```php
// Pseudocódigo para v9.10.0
if (count($lotes_array) > 15) {
    // Dividir em 2 metades
    $lotes_coluna1 = array_slice($lotes_array, 0, ceil(count($lotes_array)/2));
    $lotes_coluna2 = array_slice($lotes_array, ceil(count($lotes_array)/2));
    
    // Layout 2 colunas
    echo '<div class="cols50 fleft">'; // Tabela esquerda
    echo '<div class="cols50 fright">'; // Tabela direita
}
```

**HTML/CSS necessário:**
```html
<div style="display:flex; gap:10px;">
  <div style="flex:1;">
    <table><!-- Lotes 1-15 --></table>
  </div>
  <div style="flex:1;">
    <table><!-- Lotes 16-30 --></table>
  </div>
</div>
```

**Status:** 
- ❌ NÃO implementado nesta versão
- 📝 Comentário TODO adicionado no código
- 🎯 Planejado para v9.10.0

**Motivo:** Priorizado correções críticas (quantidade e impressão).

---

### 4. Rodapé no PDF Ajustado

**Problema:** No navegador rodapé ficava no final, mas no PDF ficava longe.

**Causa:** `margin-top:auto` não funciona bem em engines de PDF.

**Solução:**
```html
<!-- ANTES (v9.9.5): -->
<div style="flex-grow:1; min-height:20px;"></div>
<div style="margin-top:auto;">Rodapé</div>

<!-- DEPOIS (v9.9.6): -->
<div style="min-height:20px; padding-top:50px;"></div>
<div style="padding-top:10px;">Rodapé</div>
```

**Resultado:**
- ✅ Navegador: Rodapé próximo ao final
- ✅ PDF: Rodapé próximo ao final
- ✅ Consistência entre visualização e impressão

---

## 📋 Comparação de Versões

| Recurso | v9.9.5 | v9.9.6 |
|---------|--------|--------|
| Extração quantidade | ❌ Posições 8-11 (4 dígitos) | ✅ Últimos 5 dígitos (14-18) |
| Exemplo 0075942402302300170 | ❌ 230 | ✅ 170 |
| Linha amarela na impressão | ❌ Nunca aparece | ✅ Aparece se marcada |
| Cor linha na impressão | N/A | ✅ Transparente |
| Layout 2 colunas | ❌ Não | ⏳ Planejado v9.10.0 |
| Rodapé no PDF | ⚠️ Longe | ✅ Próximo ao final |

---

## 🧪 Como Testar

### Teste 1: Quantidade Correta
```bash
1. Campo "Leitura:" com foco
2. Digitar: 0075942402302300170
3. Verificar console (F12):
   ✅ "Lote extraído: 00759424"
   ✅ "Quantidade extraída: 170" (não 230)
4. Se lote não existe, linha amarela criada
5. Verificar input quantidade: 170 ✓
```

### Teste 2: Impressão de Linha Amarela
```bash
1. Adicionar lote não cadastrado (linha amarela)
2. [NÃO MARCAR] checkbox
3. Imprimir (Ctrl+P)
4. Verificar: ❌ Linha amarela NÃO aparece

5. Voltar e MARCAR checkbox
6. Imprimir novamente
7. Verificar: ✅ Linha aparece (SEM cor amarela)
```

### Teste 3: Rodapé no PDF
```bash
1. Gerar ofício
2. Na tela: verificar rodapé próximo ao final ✓
3. Gerar PDF (Ctrl+P → Salvar como PDF)
4. Abrir PDF
5. Verificar: ✅ Rodapé próximo ao final (não mais longe)
```

### Teste 4: Códigos Reais
```bash
Código 1: 0075942402302300170
  Lote:   00759424
  Qtd:    170 ✓

Código 2: 0012345612345600050
  Lote:   00123456
  Qtd:    50 ✓

Código 3: 9999999988888800001
  Lote:   99999999
  Qtd:    1 ✓
```

---

## 🔧 Arquivos Alterados

### modelo_oficio_poupa_tempo.php
**Changelog:** v9.9.6

**Principais mudanças:**
1. **L1-30:** Header atualizado com novo changelog
2. **L833-847:** CSS condicional para linhas amarelas
   ```css
   .nao-encontrado[data-checked="0"]{display:none !important;}
   .nao-encontrado[data-checked="1"]{display:table-row !important;}
   ```
3. **L1578-1589:** Extração de lote documentada
4. **L1656-1662:** Quantidade dos últimos 5 dígitos
   ```javascript
   quantidadeExtraida = parseInt(codigoLido.substring(14, 19), 10);
   ```
5. **L1683-1687:** Checkbox atualiza data-checked
6. **L1500-1504:** Rodapé com padding-top (PDF-friendly)
7. **L1436-1442:** Comentário TODO sobre layout 2 colunas

### lacres_novo.php
**Changelog:** v9.9.6
- L1-30: Sincronizado com modelo_oficio_poupa_tempo.php
- L4317: Display "Versão 9.9.6"
- L4387: Painel "(v9.9.6)"

---

## 💡 Notas Técnicas

### Por que substring(14, 19)?
```javascript
// String: 0075942402302300170
// Índices: 0123456789...14...18
//          ^^^^^^^^      ^^^^^
//          lote          quantidade

substring(14, 19) pega caracteres de índice 14 a 18 (5 chars)
Resultado: "00170"
parseInt("00170", 10) = 170
```

### Por que data-checked?
```html
<!-- HTML renderizado: -->
<tr class="linha-lote nao-encontrado" data-checked="0">

<!-- Checkbox marcado via JS: -->
novaLinha.setAttribute('data-checked', '1');

<!-- CSS seleciona baseado no atributo: -->
.nao-encontrado[data-checked="1"]{display:table-row !important;}
```

### Flexbox vs Padding para PDF
```css
/* Flexbox (funciona no navegador, não em PDF): */
flex-grow:1; margin-top:auto;

/* Padding (funciona em ambos): */
padding-top:50px;
```

---

## 📊 Estrutura Completa do Código de Barras

```
╔═══════════════════════════════════════════════════════════╗
║  CÓDIGO DE BARRAS - 19 DÍGITOS NUMÉRICOS                 ║
╠═══════════════════════════════════════════════════════════╣
║                                                           ║
║  Exemplo: 0 0 7 5 9 4 2 4 0 2 3 0 2 3 0 0 1 7 0        ║
║           ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑        ║
║           0 1 2 3 4 5 6 7 8 9 10111213141516171 8       ║
║           └───────┬─────┘ └────┬────┘ └────┬────┘        ║
║                   │            │            │             ║
║              LOTE (8)      OUTROS (6)   QUANTIDADE (5)   ║
║                                                           ║
║  Extração:                                                ║
║  - substring(0, 8)   → "00759424" → Lote                 ║
║  - substring(14, 19) → "00170"    → 170 CINs             ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
```

---

## 🚀 Próxima Versão (v9.10.0)

### Recursos Planejados:

1. **Layout 2 Colunas Automático**
   - Detectar quando `count($lotes_array) > 15`
   - Dividir lotes em 2 arrays
   - Renderizar 2 tabelas lado a lado
   - Responsivo: 1 coluna para impressão se couber

2. **Melhorias de Performance**
   - Cache de lotes em localStorage
   - Conferência offline (sync depois)
   - Reduzir re-renders desnecessários

3. **Estatísticas Avançadas**
   - Tempo médio por lote
   - Lotes por minuto
   - Gráfico de progresso

4. **Exportação de Dados**
   - Log de conferência em CSV
   - Relatório de discrepâncias
   - Timestamp de cada operação

---

## ✅ Status Final

- **Quantidade:** ✅ Últimos 5 dígitos (14-18) extraídos corretamente
- **Teste Real:** ✅ Código 0075942402302300170 → Qtd: 170 ✓
- **Impressão:** ✅ Linhas amarelas marcadas aparecem
- **Rodapé PDF:** ✅ Próximo ao final (padding-top)
- **Layout 2 Col:** ⏳ Planejado para v9.10.0
- **Versão:** ✅ 9.9.6 pronta para produção

---

**Desenvolvido por:** GitHub Copilot  
**Testado com código real:** 0075942402302300170  
**Status:** ✅ PRONTO PARA PRODUÇÃO (exceto layout 2 colunas)

---

## 🐛 Bugs Conhecidos Corrigidos

| Bug | Versão | Status |
|-----|--------|--------|
| Quantidade errada (230 ao invés de 170) | v9.9.5 | ✅ v9.9.6 |
| Linha amarela não imprime | v9.9.5 | ✅ v9.9.6 |
| Rodapé longe no PDF | v9.9.5 | ✅ v9.9.6 |
| Layout 2 colunas ausente | Todas | ⏳ v9.10.0 |
