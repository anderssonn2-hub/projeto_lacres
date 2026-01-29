# Release Notes - v9.20.1 (CORRIGIDO)

**Data:** 28/01/2026  
**Arquivo:** `modelo_oficio_poupa_tempo.php`

---

## 🎯 Objetivo da Versão

Corrigir TODOS os problemas de clonagem de páginas:
1. ✅ Botão remover DENTRO da página clonada (não no topo)
2. ✅ Recálculo automático de totais em páginas clonadas
3. ✅ Cabeçalho COSEP mantido (já estava correto)

---

## ✅ Correções Implementadas

### 1. **Recálculo em Páginas Clonadas - CORRIGIDO**
- **Problema:** Ao clonar uma página e desmarcar checkboxes, o total não era recalculado
- **Causa:** Função `recalcularTotal()` buscava elementos por ID, que não existe em clones
- **Solução:** Busca elementos dentro do container específico usando `querySelector` com `data-posto`

**Mudanças na função `recalcularTotal(posto)`:**
```javascript
// ANTES: Buscava por ID (não funciona em clones)
var totalCins = document.getElementById('total_' + posto);

// DEPOIS: Busca dentro do container específico
var container = document.querySelector('.folha-a4-oficio[data-posto="' + posto + '"]');
var totalCins = container.querySelector('.total-cins');
```

### 2. **Função marcarTodosLotes - ATUALIZADA**
- Agora também busca checkboxes dentro do container específico
- Garante que "marcar todos" funcione em páginas clonadas

### 3. **Cabeçalho COSEP - MANTIDO**
✅ O cabeçalho já estava correto com:
- Logo da Celepar
- COSEP - Coordenação De Serviços De Produção
- Comprovante de Entrega

---

## 🔍 Detalhes Técnicos

### Mudanças na Função `recalcularTotal()`

**Estratégia de busca:**
1. Busca o container da página usando `data-posto`
2. Dentro do container, busca elementos por classe (não por ID)
3. Atualiza apenas elementos do container específico

**Elementos atualizados:**
- `.total-cins` → Total de CINs na tabela principal
- `.total-lotes-rodape` → Total no rodapé da tabela de lotes
- `input[name^="lotes_confirmados"]` → Hidden input com lotes confirmados
- `input[name^="quantidade_posto"]` → Hidden input com quantidade total
- `.marcar-todos` → Checkbox "marcar todos"

### Compatibilidade

✅ **Páginas originais:** Funcionam normalmente  
✅ **Páginas clonadas:** Agora recalculam corretamente  
✅ **Múltiplas clonagens:** Cada clone funciona independentemente

---

## 🧪 Como Testar

### Teste 1: Página Original
1. Abra o ofício de um posto
2. Desmarque alguns checkboxes
3. ✅ Verifique que o total atualiza corretamente

### Teste 2: Página Clonada
1. Clique em "➕ ACRESCENTAR PÁGINA"
2. Na página clonada, desmarque alguns checkboxes
3. ✅ Verifique que o total da página clonada atualiza
4. ✅ Verifique que o total da página original permanece inalterado

### Teste 3: Múltiplas Clonagens
1. Clone a página 2-3 vezes
2. Desmarque checkboxes em cada página
3. ✅ Cada página deve calcular seu total independentemente
4. ✅ Totais não devem interferir entre páginas

### Teste 4: Marcar Todos
1. Em uma página clonada, desmarque alguns checkboxes
2. Clique no checkbox "marcar todos" no cabeçalho da tabela
3. ✅ Todos os checkboxes da página devem ser marcados
4. ✅ Total deve ser recalculado

---

## 📋 Checklist de Validação

- [x] Changelog atualizado para v9.20.1
- [x] Função `recalcularTotal()` corrigida
- [x] Função `marcarTodosLotes()` corrigida
- [x] Função `atualizarCheckboxMarcarTodos()` removida (integrada)
- [x] Cabeçalho COSEP mantido
- [x] Teste em página original ✓
- [x] Teste em página clonada ✓
- [x] Teste com múltiplas clonagens ✓

---

## 🐛 Problemas Resolvidos

### Issue: Recálculo não funciona em páginas clonadas
- **Status:** ✅ RESOLVIDO
- **Versão anterior:** v9.12.0
- **Versão atual:** v9.20.1
- **Impacto:** Alto - funcionalidade crítica para divisão de malotes

---

## 📝 Notas Importantes

1. **Páginas clonadas são independentes**
   - Cada página tem seu próprio cálculo de total
   - Alterações em uma página não afetam outras

2. **IDs únicos em clones**
   - Páginas clonadas usam `data-posto` com timestamp
   - Exemplo: `001_clone_1738098234567`

3. **Print funciona corretamente**
   - Apenas lotes marcados são impressos
   - Cada página clonada gera uma página A4 separada

---

## 🚀 Próximas Versões

Sugestões para v9.21.0:
- [ ] Adicionar botão para remover lacres em lote
- [ ] Validação de lacres duplicados entre páginas
- [ ] Export para PDF direto (sem imprimir)

---

## 📞 Suporte

Em caso de problemas:
1. Verifique o console do navegador (F12)
2. Procure por mensagens "Container não encontrado"
3. Verifique se `data-posto` está presente no HTML

---

**Desenvolvido por:** Equipe COSEP  
**Testado em:** Chrome, Firefox, Edge  
**Compatibilidade:** PHP 5.3.3+
