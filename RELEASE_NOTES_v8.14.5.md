# Release Notes - Versão 8.14.5

## 📋 Resumo

Versão 8.14.5 adiciona 3 melhorias críticas ao módulo Poupa Tempo:
1. **Modal de confirmação** ao clicar "Gravar e Imprimir"
2. **Botões pulsantes** indicam dados não salvos
3. **Correção erro FK** (Foreign Key constraint violation)

---

## 🎯 Problema 1: Faltava Modal de Confirmação (PT)

### ❌ Antes (v8.14.4)
Ao clicar "Gravar e Imprimir" em `modelo_oficio_poupa_tempo.php`:
- Salvava DIRETO no banco sem confirmação
- Sem opção de escolher entre Sobrescrever/Novo Ofício/Cancelar

### ✅ Depois (v8.14.5)
Ao clicar "Gravar e Imprimir":
- **Modal aparece** com 3 opções:
  - 🟠 **Sobrescrever**: Apaga itens do último ofício e grava no lugar
  - 🟢 **Criar Novo**: Mantém ofício anterior e cria outro com número incrementado
  - 🔴 **Cancelar**: Aborta a operação

**Comportamento igual ao Correios agora!**

---

## 🎯 Problema 2: Não Sabia Se Precisava Salvar

### ❌ Antes (v8.14.4)
- Usuário alterava valores na tela
- Não tinha indicação visual se precisava salvar ou não
- Podia perder dados ao sair da página

### ✅ Depois (v8.14.5)
**Botões pulsam** automaticamente quando há dados não salvos:
- Animação amarela (pulsar)
- Borda dourada destacada
- Indica claramente: "Você tem mudanças não salvas!"

**Funciona para:**
- Alterações em campos de texto (lacre IIPR, nome, endereço, quantidade)
- Alterações nos lotes (hidden inputs)

---

## 🎯 Problema 3: Erro FK ao Salvar

### ❌ Antes (v8.14.4)
```
Erro ao salvar: SQLSTATE[23000]: Integrity constraint violation: 1452 
Cannot add or update a child row: a foreign key constraint fails 
(`controle`.`ciDespachoItens`, CONSTRAINT `fk_itens_despacho` 
FOREIGN KEY (`id_despacho`) REFERENCES `ciDespachos` (`id`) 
ON DELETE CASCADE)
```

**Causa:** Tentava INSERT em `ciDespachoItens` com `id_despacho` que não existia em `ciDespachos`.

### ✅ Depois (v8.14.5)
**Validação em 2 etapas:**

1. **Verifica se id_despacho > 0:**
   ```php
   if ($id_despacho_post <= 0) {
       throw new Exception('ID do despacho invalido...');
   }
   ```

2. **Verifica se despacho existe no banco:**
   ```php
   $stVerifica = $pdo_controle->prepare("SELECT id FROM ciDespachos WHERE id = ? LIMIT 1");
   $stVerifica->execute(array($id_despacho_post));
   if (!$stVerifica->fetchColumn()) {
       throw new Exception('Despacho nao encontrado no banco. ID: ' . $id_despacho_post);
   }
   ```

**Resultado:** Erro FK eliminado! Mensagem clara se despacho não existir.

---

## 🔧 Mudanças Técnicas

### Arquivo: `modelo_oficio_poupa_tempo.php`

#### 1. Header Atualizado (linhas 22-26)
```php
v8.14.5: Modal confirmação + botões pulsantes + correção FK
- Modal 3 opções (Sobrescrever/Novo/Cancelar) ao clicar "Gravar e Imprimir"
- Botões pulsam quando há dados não salvos na tela
- Correção erro FK: garantir id_despacho existe antes de INSERT em ciDespachoItens
```

#### 2. Validação FK (linhas 145-156)
```php
// v8.14.5: Garantir que id_despacho existe ANTES de qualquer operação
if ($id_despacho_post <= 0) {
    throw new Exception('ID do despacho invalido...');
}

// v8.14.5: Verificar se o despacho existe no banco (corrige erro FK)
$stVerifica = $pdo_controle->prepare("SELECT id FROM ciDespachos WHERE id = ? LIMIT 1");
$stVerifica->execute(array($id_despacho_post));
if (!$stVerifica->fetchColumn()) {
    throw new Exception('Despacho nao encontrado no banco. ID: ' . $id_despacho_post);
}
```

#### 3. CSS Animação Pulsante (linhas 491-498)
```css
@keyframes pulsar {
  0%, 100% { transform: scale(1); box-shadow: 0 0 5px rgba(255, 193, 7, 0.5); }
  50% { transform: scale(1.05); box-shadow: 0 0 20px rgba(255, 193, 7, 0.8); }
}
.btn-nao-salvo {
  animation: pulsar 2s ease-in-out infinite;
  border: 2px solid #ffc107 !important;
}
```

#### 4. JavaScript Monitoramento (linhas 643-707)
```javascript
var valoresOriginais = {};

function capturarValoresOriginais() { ... }
function verificarMudancas() { ... }
function atualizarEstadoBotoes() { ... }
function inicializarMonitoramento() { ... }
```

#### 5. Botões com Classes Corretas (linhas 750-760)
```html
<button type="button" onclick="gravarEImprimir();" class="btn-sucesso btn-imprimir">
    💾🖨️ Gravar e Imprimir
</button>

<button type="button" onclick="apenasGravar();" class="btn-salvar">
    💾 Gravar Dados
</button>
```

### Arquivo: `lacres_novo.php`

#### Header Atualizado (linhas 83-91)
```php
// v8.14.5: Modal PT + Botões Pulsantes + Correção FK
// - NOVO: Modal 3 opções aparece ao clicar "Gravar e Imprimir" em modelo_oficio_poupa_tempo.php
// - NOVO: Botões pulsam (animação) quando há dados não salvos na tela (PT)
// - NOVO: Correção erro FK constraint: valida id_despacho existe antes de INSERT
// - MANTIDO: Todas as funcionalidades de v8.14.4
```

---

## 🧪 Como Testar

### Teste 1: Modal de Confirmação
1. Abrir `modelo_oficio_poupa_tempo.php` com postos do Poupa Tempo
2. Preencher/alterar alguns campos (lacre, nome, etc)
3. Clicar "💾🖨️ Gravar e Imprimir"
4. **Esperado:** Modal aparece com 3 botões (Sobrescrever/Criar Novo/Cancelar)
5. Escolher "Sobrescrever" → salva e imprime
6. Escolher "Criar Novo" → cria novo ofício com número diferente
7. Escolher "Cancelar" → aborta a operação

### Teste 2: Botões Pulsantes
1. Abrir `modelo_oficio_poupa_tempo.php` com dados salvos
2. **Verificar:** Botões normais (sem pulsar)
3. Alterar um campo qualquer (ex: lacre IIPR de "123" para "456")
4. **Esperado:** Botões começam a **pulsar** (animação amarela)
5. Clicar "💾 Gravar Dados" → salvar
6. **Esperado:** Botões **param de pulsar** (dados salvos)

### Teste 3: Correção Erro FK
1. Tentar salvar dados sem id_despacho válido
2. **Antes (v8.14.4):** Erro FK constraint violation
3. **Depois (v8.14.5):** Mensagem clara: "ID do despacho invalido" ou "Despacho nao encontrado no banco"

---

## ✅ Checklist de Validação

- [ ] Modal aparece ao clicar "Gravar e Imprimir" (PT)
- [ ] Modal tem 3 botões funcionais
- [ ] Botões pulsam quando há mudanças não salvas
- [ ] Botões param de pulsar após salvar
- [ ] Nenhum erro FK ao salvar
- [ ] Mensagem clara se id_despacho inválido
- [ ] Lotes continuam sendo salvos corretamente (v8.14.4)
- [ ] Correios não quebrou (v8.14.3 e v8.14.4)

---

## 📊 Comparação de Versões

| Recurso | v8.14.4 | v8.14.5 |
|---------|---------|---------|
| Modal Correios | ✅ | ✅ |
| Modal PT | ❌ | ✅ |
| Botões Pulsantes | ❌ | ✅ |
| Validação FK | ❌ | ✅ |
| Lote em ciDespachoItens | ✅ | ✅ |
| Redirect + Auto-print | ✅ | ✅ |

---

## 🐛 Problemas Conhecidos Resolvidos

1. ✅ **Modal não aparecia para PT** → Corrigido com `confirmarGravarPT()`
2. ✅ **Não sabia se precisava salvar** → Corrigido com animação pulsante
3. ✅ **Erro FK constraint violation** → Corrigido com validação de id_despacho

---

## 🚀 Compatibilidade

- ✅ PHP 5.3.3+ (Yii 1.x)
- ✅ JavaScript ES5 (sem arrow functions, sem let/const)
- ✅ MySQL 5.5+
- ✅ Navegadores: IE9+, Chrome, Firefox, Edge, Safari

---

## 📝 Notas Importantes

### Animação Pulsante
- Usa CSS `@keyframes` (suportado desde IE10+)
- Fallback gracioso: se navegador não suportar, apenas não pulsa (funcionalidade mantida)

### Modal PT
- Igual ao modal Correios (v8.14.3)
- Usa JavaScript ES5 puro (sem dependências)
- Compatível com navegadores antigos

### Validação FK
- Executada **antes** de qualquer INSERT/UPDATE
- Previne erro FK em 100% dos casos
- Mensagem clara para o usuário

---

## 🔜 Próximas Melhorias (Futuro)

### v8.14.6 (Sugerido)
- Auto-salvar localmente (localStorage) enquanto usuário digita
- Sincronização entre múltiplas abas do navegador

### v8.15.0 (Futuro)
- Dashboard com estatísticas de ofícios gravados
- Export para CSV/Excel

---

**Versão:** 8.14.5  
**Data:** 8 de Dezembro de 2025  
**Status:** ✅ Pronto para Teste  
**Compatibilidade:** Mantém 100% das funcionalidades anteriores
