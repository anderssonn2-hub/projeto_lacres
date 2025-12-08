# Guia de Teste - Versão 8.11

## 🎯 Objetivo

Validar que dados (lacres IIPR, lacres Correios, etiquetas Correios) persistem em localStorage quando:
1. Um posto é excluído da grade
2. Um filtro por data é aplicado
3. A página é recarregada

## 📋 Pré-requisitos

- PHP rodando com `php -S localhost:8000 -t .`
- Navegador moderno (Chrome, Firefox, Edge, Safari)
- Despacho existente no banco com múltiplos postos (de preferência 3+ postos)

## 🧪 Teste 1: Exclusão de Posto (5 min)

### Passos

1. **Abrir página:**
   ```
   http://localhost:8000/lacres_novo.php
   ```

2. **Preencher dados em 3 postos diferentes:**
   - Posto 1: Lacre IIPR = "11111", Lacre Correios = "22222", Etiqueta = "12345678901234567890123456789012345"
   - Posto 2: Lacre IIPR = "33333", Lacre Correios = "44444", Etiqueta = "abcdefghijklmnopqrstuvwxyz01234567"
   - Posto 3: Lacre IIPR = "55555", Lacre Correios = "66666", Etiqueta = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456"

3. **Abrir DevTools (F12):**
   - Ir para aba "Application" (Chrome) ou "Storage" (Firefox)
   - Expandir "Local Storage"
   - Procurar por entradas começando com "oficioCorreios"
   - **Esperado:** Deve ver 3 entradas (uma para cada posto)

4. **Excluir Posto 2:**
   - Clicar no botão "Excluir" da linha do Posto 2
   - Confirmar a exclusão no dialog

5. **Verificar resultado:**
   - Página recarrega com apenas 2 postos (Posto 1 e Posto 3)
   - **✅ Esperado:** Valores do Posto 1 e Posto 3 ainda aparecem nos inputs
   - **❌ Falha:** Inputs vazios (localStorage não restaurou)

6. **Verificar localStorage:**
   - DevTools → Local Storage → Deve ter 2 entradas (Postos 1 e 3)
   - *(Entrada do Posto 2 pode estar lá, mas não será exibida porque a linha foi removida)*

### Resultado de Sucesso
```
✅ Posto 1: IIPR=11111, Correios=22222, Etiqueta=12345...
✅ Posto 3: IIPR=55555, Correios=66666, Etiqueta=ABCDE...
✅ localStorage tem 2-3 entradas "oficioCorreios:..."
```

---

## 🧪 Teste 2: Filtro por Data (5 min)

### Passos

1. **Preencher dados em 2 postos:**
   - Posto A: Etiqueta = "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
   - Posto B: Etiqueta = "BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB"

2. **Abrir DevTools (F12) → Local Storage:**
   - Verificar que "oficioCorreios:..." existe para Postos A e B

3. **Aplicar filtro por data:**
   - Desmarcar todas as datas EXCETO uma ou duas
   - Clicar "Filtrar por data(s)"

4. **Verificar resultado:**
   - Página recarrega com nova grade (apenas datas selecionadas)
   - **✅ Esperado:** Valores do Posto A e Posto B ainda aparecem
   - **❌ Falha:** Inputs vazios

5. **Verificar localStorage:**
   - DevTools → Local Storage → Deve ter entradas para Postos A e B

### Resultado de Sucesso
```
✅ Posto A: Etiqueta = AAAA...
✅ Posto B: Etiqueta = BBBB...
✅ localStorage inalterado
```

---

## 🧪 Teste 3: Recarregar Página (3 min)

### Passos

1. **Preencher dados em 1 posto:**
   - Etiqueta = "TESTESTESTESTESTESTESTESTESTESTE"

2. **Pressionar F5 (recarregar página)**

3. **Verificar resultado:**
   - **✅ Esperado:** Valor da Etiqueta ainda aparece no input
   - **❌ Falha:** Input vazio

### Resultado de Sucesso
```
✅ Etiqueta = TESTE...
✅ localStorage intacto
```

---

## 🧪 Teste 4: Verificar localStorage via DevTools (2 min)

### Chrome

1. F12 → "Application" tab
2. Left sidebar → "Local Storage"
3. Clicar na URL (ex: `http://localhost:8000`)
4. Ver chaves como:
   ```
   oficioCorreios::0950:8005
   oficioCorreios::0950:8010
   ```
5. Clicar em uma chave para ver o valor:
   ```
   {
     "lacre_iipr": "12345",
     "lacre_correios": "67890",
     "etiqueta_correios": "12345678901234567890123456789012345"
   }
   ```

### Firefox

1. F12 → "Storage" tab
2. Left sidebar → "Local Storage" → `http://localhost:8000`
3. Ver mesmas chaves e valores

### Safari

1. Develop menu → "Show Web Inspector"
2. "Storage" tab → "Local Storage"
3. Selecionar `http://localhost:8000`

---

## 🧪 Teste 5: Limpar localStorage e Recarregar (2 min)

### Passos

1. **Console (F12 → Console):**
   ```javascript
   localStorage.clear()
   ```

2. **Pressionar F5**

3. **Verificar resultado:**
   - **✅ Esperado:** Inputs vazios (localStorage foi limpo)
   - **❌ Falha:** Página quebra ou erro no console

### Resultado de Sucesso
```
✅ Página funciona normalmente
✅ Inputs vazios
✅ Nenhum erro no console
```

---

## 🧪 Teste 6: Múltiplos Postos / Múltiplas Regionais (10 min)

### Passos

1. **Preencher dados em 5+ postos de diferentes regionais:**
   - Regional 0950: Postos 8005, 8010, 8015
   - Regional 0955: Postos 8020, 8025

2. **Verificar localStorage:**
   - DevTools → Local Storage
   - Deve ter 5 entradas diferentes
   - Cada uma com sua chave única (regional diferente)

3. **Excluir 1 posto da Regional 0950:**
   - Remover Posto 8010
   - **Esperado:** Postos 8005 e 8015 mantêm dados, todos de Regional 0955 mantêm dados

4. **Filtrar para apenas Regional 0955:**
   - Alterar filtro de postos/regionais
   - **Esperado:** Dados de 0955 restaurados, dados de 0950 não aparecem (porque postos foram removidos da grade)

### Resultado de Sucesso
```
✅ localStorage tem 4 entradas após exclusão (1 foi removida)
✅ localStorage tem 5 entradas antes de filtrar
✅ Todos os dados restauram corretamente após cada ação
```

---

## 📊 Checklist de Validação

| # | Aspecto | Passa | Falha | Notas |
|---|---------|-------|-------|-------|
| 1 | Exclusão preserva dados | ☐ | ☐ | |
| 2 | Filtro preserva dados | ☐ | ☐ | |
| 3 | Recarregar restaura dados | ☐ | ☐ | |
| 4 | localStorage tem chaves corretas | ☐ | ☐ | |
| 5 | localStorage tem valores corretos (JSON) | ☐ | ☐ | |
| 6 | Limpar localStorage não quebra página | ☐ | ☐ | |
| 7 | Múltiplas regionais funcionam | ☐ | ☐ | |
| 8 | Console sem erros | ☐ | ☐ | |
| 9 | SPLIT CENTRAL IIPR ainda funciona | ☐ | ☐ | |
| 10 | Validação de etiqueta duplicada funciona | ☐ | ☐ | |

---

## 🐛 Debug / Troubleshooting

### localStorage não está salvando nada

1. **Verificar se localStorage está habilitado:**
   ```javascript
   typeof window.localStorage !== 'undefined' && localStorage !== null
   // Deve retornar true
   ```

2. **Verificar se há erro de quota (localStorage cheio):**
   ```javascript
   try {
       localStorage.setItem('test', '1');
       localStorage.removeItem('test');
       console.log('localStorage ok');
   } catch (e) {
       console.error('localStorage erro:', e.message);
   }
   ```

3. **Modo privado:** Se estiver em modo privado/incógnito, localStorage pode não funcionar. Testar em modo normal.

### Valores não estão sendo restaurados

1. **Verificar se a função está sendo chamada:**
   ```javascript
   // Console, ao recarregar
   window.restaurarEstadoEtiquetasCorreios()
   ```

2. **Verificar se as chaves localStorage existem:**
   ```javascript
   // Console
   for (var i = 0; i < localStorage.length; i++) {
       console.log(localStorage.key(i), JSON.parse(localStorage.getItem(localStorage.key(i))));
   }
   ```

3. **Verificar se os seletores estão corretos:**
   ```javascript
   // Console
   console.log(document.querySelectorAll('tr[data-posto-codigo]').length);
   // Deve ser > 0
   ```

### localStorage vazio, mas etiquetas aparecem

Pode estar usando o `$_SESSION` do PHP (fallback antigo). localStorage é novo (v8.11). Isso é OK - o código preserva ambos.

---

## ✅ Sign-Off de Teste

Quando todos os testes passarem, preencher:

```
Testado em: [data/hora]
Navegador: [Chrome/Firefox/Safari/Edge] v[versão]
Servidor: [PHP versão]
Resultado: [PASSOU / FALHOU]

Assinado por: [seu nome]
```

---

## 📚 Referências

- localStorage MDN: https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage
- JSON.stringify/parse: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON
- Código-fonte: `lacres_novo.php` linhas 3627-3723 (funções), 3238-3252 (formulário filtro), 3740-3751 (exclusão), 3872 (init)
