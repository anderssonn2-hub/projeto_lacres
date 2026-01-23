# Guia de Teste - Versão 9.7.1

## ✅ Checklist de Validação

### 1. 📅 Teste de Filtros de Data por Período

**Objetivo:** Validar que o filtro por intervalo de datas funciona corretamente

**Passos:**
1. Abrir `lacres_novo.php` no navegador
2. Localizar a seção "🗓️ Filtrar por Período" (abaixo dos campos de lacre)
3. Preencher:
   - **Data Inicial:** 15/01/2026
   - **Data Final:** 23/01/2026
4. Clicar em "Aplicar Período"

**Resultado Esperado:**
- ✅ Página recarrega com filtro aplicado
- ✅ Exibe apenas postos das datas no intervalo selecionado
- ✅ Sessão `$_SESSION['datas_filtro']` atualizada com datas do BD no intervalo
- ✅ Se não houver datas no intervalo, mostra página vazia (sem erro)

**Casos de Teste:**
- [ ] Intervalo com datas válidas que existem no BD
- [ ] Intervalo com datas que NÃO existem no BD (deve retornar vazio)
- [ ] Data inicial > data final (deve funcionar invertido ou exibir todas)
- [ ] Campos vazios (deve usar checkboxes manuais - comportamento antigo)

---

### 2. 📊 Teste do Indicador de Dias com/sem Conferência

**Objetivo:** Validar que o indicador mostra corretamente os status de conferência

**Passos:**
1. Abrir `lacres_novo.php` no navegador
2. Observar o painel fixo no **canto superior direito** da tela
3. Verificar conteúdo:
   - Título: "📅 Status de Conferências"
   - Seção 1: "✓ Com Conferência" (em verde)
   - Seção 2: "✗ Sem Conferência" (em vermelho)

**Resultado Esperado:**
- ✅ Painel visível e fixo (não se move ao rolar a página)
- ✅ Lista até 5 datas por seção
- ✅ Se houver mais de 5, mostra "(+N mais)" em cinza
- ✅ Datas no formato `dd/mm/aaaa`
- ✅ Cores corretas (verde para conferidos, vermelho para não conferidos)
- ✅ Se não houver dados, mostra "Nenhum"

**Validação SQL Manual:**
```sql
-- Verificar dias com conferência (últimos 30 dias)
SELECT DISTINCT DATE(dataCarga) as data 
FROM ciPostosCsv 
WHERE dataCarga >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY data DESC
LIMIT 15;
```

**Casos de Teste:**
- [ ] BD com dados recentes (últimos 7 dias)
- [ ] BD com dados esparsos (alguns dias com dados, outros sem)
- [ ] BD vazio (deve mostrar "Nenhum" em ambas as seções)

---

### 3. 🎯 Teste do Pop-up Centralizado para Etiquetas

**Objetivo:** Validar que o pop-up aparece corretamente e auxilia na leitura

**Passos:**
1. Abrir `lacres_novo.php` no navegador
2. Selecionar algumas datas para exibir postos Correios (CAPITAL ou REGIONAIS)
3. Clicar/focar em um **input de Etiqueta Correios** (coluna "Etiqueta Correios")

**Resultado Esperado:**
- ✅ Pop-up aparece imediatamente no centro da tela
- ✅ Fundo roxo com gradiente (#667eea → #764ba2)
- ✅ Exibe título: "🎯 Leitura de Etiqueta"
- ✅ Exibe nome do posto atual (ex: "POSTO 042 - CASCAVEL")
- ✅ Exibe posição: "Posto X de Y"
- ✅ Exibe instrução: "📦 Escaneie o código de barras da etiqueta (35 dígitos)"
- ✅ Animação suave de entrada (slide + fade)

**Teste de Digitação/Scanner:**
1. Com o pop-up aberto, começar a digitar números
2. Observar o contador: "Posto X de Y • N/35 dígitos"
3. Digitar 35 dígitos consecutivos

**Resultado Esperado ao Digitar:**
- ✅ Contador atualiza em tempo real a cada dígito
- ✅ Ao atingir 35 dígitos:
  - Pop-up fecha automaticamente
  - Validação de duplicatas executa
  - Se válido, foco avança para próximo posto
  - Se inválido, campo limpa e foco permanece
- ✅ Novo pop-up abre automaticamente para o próximo posto

**Teste de Blur:**
1. Clicar fora do input (ou pressionar Tab)

**Resultado Esperado:**
- ✅ Pop-up fecha imediatamente
- ✅ Não interfere na validação de duplicatas

**Casos de Teste:**
- [ ] CAPITAL - postos com etiquetas únicas
- [ ] REGIONAIS - postos de diferentes regionais
- [ ] CENTRAL IIPR - deve funcionar SEM validação de duplicatas
- [ ] Teste com scanner de código de barras real (35 dígitos)
- [ ] Teste com digitação manual (letra + números)
- [ ] Teste de navegação via Tab entre inputs

---

### 4. 🔄 Teste de Integração (Fluxo Completo)

**Objetivo:** Validar que todas as funcionalidades trabalham juntas

**Passos:**
1. **Filtrar por Período:**
   - Data Inicial: 20/01/2026
   - Data Final: 23/01/2026
   - Clicar "Aplicar Período"

2. **Verificar Indicador de Dias:**
   - Confirmar que mostra dias corretos
   - Pelo menos uma data do intervalo deve aparecer em "Com Conferência"

3. **Preencher Etiquetas:**
   - Para cada posto CAPITAL/REGIONAIS visível:
     - Clicar no input de etiqueta
     - Verificar pop-up
     - Escanear/digitar 35 dígitos
     - Confirmar auto-avançamento

4. **Gravar Ofício:**
   - Clicar "Gravar e Imprimir Correios"
   - Confirmar modal
   - Escolher "Sobrescrever" ou "Criar Novo"

**Resultado Esperado:**
- ✅ Todas as funcionalidades trabalham sem conflito
- ✅ Pop-up não interfere no salvamento
- ✅ Indicador de dias permanece visível durante todo o fluxo
- ✅ Filtros aplicados corretamente
- ✅ Dados salvos em `ciDespachoLotes` com etiquetas corretas

---

## 🐛 Checklist de Regressão

Validar que funcionalidades antigas continuam funcionando:

- [ ] Auto-avançamento entre postos (após 35 dígitos)
- [ ] Validação de duplicatas (CAPITAL + REGIONAIS, exceto CENTRAL)
- [ ] Botão SPLIT da CENTRAL IIPR
- [ ] Propagação de lacres/etiquetas por regional
- [ ] Salvamento em ciDespachoLotes (etiquetaiipr, etiquetacorreios, etiqueta_correios)
- [ ] Impressão de PDF (com lacres corretos)
- [ ] Modal de confirmação (Sobrescrever/Criar Novo/Cancelar)
- [ ] Sistema de snapshot (auto-save a cada 3s)
- [ ] Limpar sessão (zera todos os inputs)

---

## 📊 Métricas de Performance

**Tempo de carregamento esperado:**
- Query de dias com conferência: < 500ms
- Query de filtro por período: < 300ms
- Renderização do pop-up: < 50ms

**Compatibilidade testada:**
- PHP: 5.3.3 (Yii 1.x)
- JavaScript: ES5 (sem arrow functions)
- MySQL: 5.5+

---

## 🔍 Logs para Debug

**Console do Navegador:**
```javascript
// Verificar se funções globais existem
console.log(typeof window.mostrarPopupEtiqueta);   // "function"
console.log(typeof window.ocultarPopupEtiqueta);   // "function"
console.log(typeof window.atualizarProgressoPopup); // "function"
```

**PHP - Debug de Filtro de Datas:**
```php
// Adicionar temporariamente após linha 2310
echo '<pre>';
print_r($_SESSION['datas_filtro']);
echo '</pre>';
```

---

## ✅ Critérios de Aceitação

A versão 9.7.1 está pronta para produção se:

1. ✅ Todos os testes funcionais passaram
2. ✅ Checklist de regressão completo
3. ✅ Nenhum erro no console do navegador
4. ✅ Nenhum erro PHP exibido na tela
5. ✅ Performance dentro dos limites esperados
6. ✅ Compatibilidade validada no ambiente alvo (PHP 5.3.3)

---

## 📝 Notas Finais

- **Backup:** Sempre fazer backup do arquivo anterior antes de deploy
- **Cache:** Limpar cache do navegador após atualização
- **Produção:** Testar primeiro em ambiente de homologação
- **Rollback:** Manter arquivo anterior disponível para rollback rápido

---

**Data de Criação:** 23/01/2026  
**Versão:** 9.7.1  
**Status:** ✅ Pronto para Teste
