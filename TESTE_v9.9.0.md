# 🧪 Guia Rápido de Teste - v9.9.0

## ✅ CHECKLIST DE VALIDAÇÃO

### Teste 1: Layout Centralizado
- [ ] Abrir ofício Poupa Tempo gerado
- [ ] Verificar tabela principal está centralizada
- [ ] Verificar que não ultrapassa margem direita
- [ ] Comparar com imagem de referência (BOA VISTA)

**Esperado:** Tabela com max-width 650px, centralizada

---

### Teste 2: Conferência Básica
1. [ ] Gerar ofício com pelo menos 3 lotes
2. [ ] Verificar painel de conferência aparece
3. [ ] Campo de leitura tem foco automático
4. [ ] Ler código do primeiro lote:
   - [ ] Linha fica verde
   - [ ] Contador "Conferidos" incrementa (0→1)
   - [ ] Contador "Pendentes" decrementa
   - [ ] Campo limpa automaticamente
5. [ ] Ler segundo lote:
   - [ ] Linha fica verde
   - [ ] Contadores atualizam
6. [ ] Tentar ler lote já conferido:
   - [ ] Alerta: "Este lote já foi conferido!"
   - [ ] Linha permanece verde (não duplica)

**Esperado:** Sistema valida e marca lotes corretamente

---

### Teste 3: Lote Não Cadastrado (Amarelo)
1. [ ] No campo de conferência, digitar: `999999`
2. [ ] Pressionar Enter
3. [ ] Verificar:
   - [ ] Nova linha amarela criada
   - [ ] Texto: "999999 (NÃO CADASTRADO)"
   - [ ] Checkbox desmarcado
   - [ ] Campo quantidade = 0 (editável)
   - [ ] Alerta: "Lote não estava na lista!"
4. [ ] Editar quantidade para 10
5. [ ] Marcar checkbox
6. [ ] Verificar total recalcula (inclui +10)

**Esperado:** Lotes extras detectados e documentados

---

### Teste 4: Atalhos de Teclado
1. [ ] Clicar fora do campo de conferência
2. [ ] Pressionar **Alt+C**
3. [ ] Verificar foco volta para campo de conferência
4. [ ] Digitar número de lote
5. [ ] Pressionar **Enter** (não clicar em botão)
6. [ ] Verificar lote é conferido

**Esperado:** Atalhos funcionam corretamente

---

### Teste 5: Filtro de Lotes na Impressão
1. [ ] Conferir 2 lotes (ficam verdes)
2. [ ] Criar 1 lote extra amarelo (deixar desmarcado)
3. [ ] Desmarcar 1 lote original (checkbox)
4. [ ] Nota: Total deve recalcular automaticamente
5. [ ] Abrir preview de impressão (Ctrl+P)
6. [ ] Verificar na impressão:
   - [ ] Apenas 1 lote aparece (o marcado)
   - [ ] Lote desmarcado NÃO aparece
   - [ ] Lote amarelo (desmarcado) NÃO aparece
   - [ ] Total mostra apenas o lote marcado

**Esperado:** Somente lotes marcados na impressão

---

### Teste 6: Impressão Limpa (Sem Cores/Controles)
1. [ ] Conferir alguns lotes (ficam verdes)
2. [ ] Criar lote extra (fica amarelo)
3. [ ] Abrir preview de impressão (Ctrl+P)
4. [ ] Verificar que NÃO aparece:
   - [ ] Cores (verde/amarelo) → Todas as linhas brancas
   - [ ] Painel de conferência
   - [ ] Campo de leitura
   - [ ] Contadores (Total/Conferidos/Pendentes)
   - [ ] Checkboxes
   - [ ] Coluna de checkbox
5. [ ] Verificar que APARECE:
   - [ ] Cabeçalho institucional
   - [ ] Tabela principal (Poupatempo, Quantidade, Lacre)
   - [ ] Tabela de lotes (Lote | Quantidade)
   - [ ] Total de carteiras

**Esperado:** Impressão profissional e limpa

---

### Teste 7: Uniformização de Fonte
1. [ ] Comparar tamanho de fonte:
   - [ ] Nome do posto (ex: BOA VISTA)
   - [ ] Número do lote
   - [ ] Quantidade
   - [ ] Cabeçalhos da tabela
2. [ ] Verificar todos estão em **14px**
3. [ ] Verificar negrito onde apropriado

**Esperado:** Fonte consistente em todo o documento

---

### Teste 8: Contadores em Tempo Real
1. [ ] Gerar ofício com 5 lotes
2. [ ] Verificar contadores iniciais:
   - Total: 5
   - Conferidos: 0
   - Pendentes: 5
3. [ ] Conferir 1 lote → Verificar:
   - Total: 5
   - Conferidos: 1
   - Pendentes: 4
4. [ ] Conferir mais 2 lotes → Verificar:
   - Total: 5
   - Conferidos: 3
   - Pendentes: 2
5. [ ] Criar lote extra (amarelo) → Verificar:
   - Total: 6 (incrementa)
   - Conferidos: 3
   - Pendentes: 3
6. [ ] Conferir os 3 restantes
7. [ ] Verificar alerta: "✅ Todos os lotes foram conferidos!"

**Esperado:** Contadores precisos e alerta final

---

### Teste 9: Fluxo Completo
1. [ ] Gerar ofício Poupa Tempo
2. [ ] Conferir todos os lotes via scanner
3. [ ] Criar 1 lote extra e definir quantidade
4. [ ] Decidir se marca ou desmarca lote extra
5. [ ] Clicar "Gravar e Imprimir"
6. [ ] Verificar impressão final
7. [ ] Documento pronto para assinatura

**Esperado:** Fluxo sem interrupções

---

### Teste 10: Cenário Real (Scanner Físico)
**Pré-requisito:** Scanner de código de barras conectado

1. [ ] Conectar scanner USB
2. [ ] Abrir ofício Poupa Tempo
3. [ ] Campo de conferência tem foco automático
4. [ ] Escanear código de barras físico
5. [ ] Verificar sistema reconhece instantaneamente
6. [ ] Linha fica verde automaticamente
7. [ ] Campo limpa e está pronto para próximo scan
8. [ ] Repetir para todos os lotes físicos
9. [ ] Verificar velocidade (deve ser rápido)

**Esperado:** Operação fluida com hardware real

---

## 🐛 Problemas Conhecidos a Testar

### Caso 1: Código com Espaços
**Teste:** Digite "12345 " (com espaço no final)  
**Esperado:** Sistema faz trim() e encontra "12345"  
**Ação se falhar:** Reportar bug

### Caso 2: Código com Zeros à Esquerda
**Teste:** Lote "00123" vs "123"  
**Esperado:** Sistema trata como strings (não converte)  
**Ação se falhar:** Verificar atributo data-lote

### Caso 3: Enter Rápido (Spam)
**Teste:** Pressionar Enter várias vezes rapidamente  
**Esperado:** Sistema processa apenas quando há código  
**Ação se falhar:** Adicionar debounce

### Caso 4: Navegação com Tab
**Teste:** Pressionar Tab no campo de conferência  
**Esperado:** Foco vai para próximo elemento (não confere)  
**Ação se falhar:** Evento só no Enter

---

## 📊 Métricas de Sucesso

Após todos os testes, verificar:

- [ ] **Performance:** Conferência de 10 lotes em < 30 segundos
- [ ] **Precisão:** 100% dos lotes conferidos corretamente
- [ ] **Usabilidade:** Operador não precisa usar mouse
- [ ] **Impressão:** Layout profissional e limpo
- [ ] **Confiabilidade:** Zero erros de validação

---

## ✅ Aprovação Final

### Aprovador: _________________  
### Data: ___ / ___ / ______  
### Assinatura: _________________

### Observações:
```
[Espaço para notas do teste]





```

---

## 🚀 Próximos Passos Após Aprovação

1. [ ] Treinar operadores no novo sistema
2. [ ] Criar procedimento operacional padrão (POP)
3. [ ] Monitorar primeiras conferências
4. [ ] Coletar feedback dos usuários
5. [ ] Planejar melhorias para v9.10.0

---

**Versão do Teste:** v9.9.0  
**Data:** 27/01/2026  
**Responsável:** ___________________
