# ✅ Checklist de Teste - v9.20.1

## 🎯 O que foi corrigido nesta versão

### 1. ✅ Cabeçalho COSEP
- **Status:** ✅ JÁ ESTAVA CORRETO desde v9.12.0
- **Localização:** Linha 1391-1401 do arquivo
- **Conteúdo:**
  ```
  COSEP
  Coordenacao De Servicos De Producao
  
  Comprovante de Entrega
  ```
- **Logo:** `logo_celepar.png` (250x55px)

### 2. ✅ Botão REMOVER dentro da página clonada
- **Status:** ✅ CORRIGIDO
- **Antes:** Botão aparecia no topo da tela
- **Agora:** Botão aparece DENTRO da página clonada, logo no início
- **Visual:** Fundo amarelo (#fff3cd), borda laranja (#ffc107), botão vermelho

### 3. ✅ Recálculo automático de totais
- **Status:** ✅ CORRIGIDO
- **Como funciona:**
  - Função `recalcularTotal(posto)` busca o container específico usando `data-posto`
  - Atualiza APENAS os elementos dentro daquele container
  - Funciona tanto em páginas originais quanto clonadas

---

## 🧪 Como Testar (PASSO A PASSO)

### Teste 1: Verificar Cabeçalho COSEP
1. Abra o arquivo `modelo_oficio_poupa_tempo.php` no navegador
2. ✅ Verifique que o cabeçalho mostra:
   - Logo da Celepar à esquerda
   - COSEP no centro
   - "Coordenacao De Servicos De Producao"
   - "Comprovante de Entrega"
3. ❌ NÃO deve mostrar "GOVERNO DO ESTADO DE SAO PAULO"

### Teste 2: Página Original
1. Abra um ofício de qualquer posto
2. Observe o total de CINs na tabela principal (exemplo: 1.234)
3. **DESMARQUE** alguns checkboxes
4. ✅ O total deve atualizar automaticamente
5. ✅ O total no rodapé da tabela também deve atualizar

### Teste 3: Botão de Clonagem
1. Na página de um posto, clique em "➕ ACRESCENTAR PÁGINA"
2. ✅ Uma confirmação deve aparecer
3. ✅ Uma nova página deve ser criada abaixo da original
4. ✅ A página deve rolar automaticamente até a nova página

### Teste 4: Botão REMOVER dentro da página clonada
1. Após clonar, observe a página clonada
2. ✅ No **TOPO** da página clonada (dentro dela, não flutuando), deve haver:
   - Um container com fundo amarelo
   - Texto de alerta/aviso
   - Botão vermelho "✕ REMOVER ESTA PÁGINA CLONADA"
3. ❌ NÃO deve haver botão flutuando no topo da tela
4. Clique no botão remover
5. ✅ Deve pedir confirmação
6. ✅ Ao confirmar, a página clonada deve ser removida

### Teste 5: Recálculo em Página Clonada (TESTE CRÍTICO)
1. Clone uma página
2. Na **PÁGINA CLONADA**, desmarque 2-3 checkboxes
3. ✅ O total da PÁGINA CLONADA deve atualizar imediatamente
4. ✅ O total da PÁGINA ORIGINAL não deve mudar
5. Volte para a **PÁGINA ORIGINAL**
6. Desmarque 2-3 checkboxes
7. ✅ O total da PÁGINA ORIGINAL deve atualizar
8. ✅ O total da PÁGINA CLONADA não deve mudar

### Teste 6: Múltiplas Clonagens
1. Clone a mesma página 2-3 vezes
2. Em cada página (original + clones), desmarque checkboxes diferentes
3. ✅ Cada página deve ter seu próprio total independente
4. ✅ Marcar/desmarcar em uma página não afeta outras

### Teste 7: Checkbox "Marcar Todos"
1. Em uma página clonada, clique no checkbox no cabeçalho da tabela
2. ✅ Todos os checkboxes devem ser marcados/desmarcados
3. ✅ O total deve ser recalculado automaticamente

### Teste 8: Impressão
1. Após clonar e ajustar checkboxes, pressione Ctrl+P (ou Cmd+P no Mac)
2. ✅ O botão "REMOVER" deve estar OCULTO na impressão
3. ✅ Cada página (original + clones) deve gerar uma folha A4 separada
4. ✅ Apenas lotes marcados devem aparecer na impressão

---

## 🔍 Debug (se algo não funcionar)

### Se o total não atualizar na página clonada:
1. Abra o Console do navegador (F12)
2. Procure por mensagens de erro
3. Verifique se aparece: `Container não encontrado para posto: XXX`
4. Se aparecer, anote o código do posto e reporte

### Se o botão remover não aparecer:
1. Verifique se há um container amarelo no topo da página clonada
2. Se não houver, abra F12 e procure por erros JavaScript
3. Anote qualquer erro e reporte

### Se aparecer "GOVERNO DO ESTADO" no cabeçalho:
1. O arquivo não foi atualizado corretamente
2. Faça refresh forçado: Ctrl+Shift+R (ou Cmd+Shift+R no Mac)
3. Se persistir, o arquivo PHP no servidor não está atualizado

---

## 📊 Resultados Esperados

| Item | Status Esperado |
|------|----------------|
| Cabeçalho COSEP | ✅ Visível com logo |
| Cabeçalho Governo SP | ❌ Não deve aparecer |
| Botão remover na página | ✅ Dentro da página clonada |
| Botão remover no topo | ❌ Não deve existir |
| Total atualiza (original) | ✅ Sim |
| Total atualiza (clone) | ✅ Sim |
| Totais independentes | ✅ Sim |
| Botão oculto na impressão | ✅ Sim |
| Múltiplas clonagens | ✅ Funcionam |

---

## ✅ Confirmação Final

Após completar TODOS os testes acima, responda:

- [ ] Cabeçalho mostra COSEP (não Governo SP)?
- [ ] Botão remover aparece DENTRO da página clonada?
- [ ] Total atualiza quando desmarco checkboxes na página clonada?
- [ ] Total da página original não muda quando altero página clonada?
- [ ] Consegui criar múltiplas clonagens sem problemas?
- [ ] Botão remover fica oculto na impressão (Ctrl+P)?

Se TODOS os itens acima estiverem ✅, a versão 9.20.1 está funcionando corretamente!

---

## 🐛 Se houver problemas

Me informe:
1. Qual teste falhou?
2. O que aconteceu (ou não aconteceu)?
3. Alguma mensagem de erro apareceu? (tire print)
4. Qual navegador está usando?

Com essas informações, posso corrigir rapidamente! 🚀
