# Release Notes - Versão 8.11

## Resumo

Versão 8.11 implementa persistência de dados em localStorage para preservar lacres (IIPR/Correios) e etiquetas dos Correios ao:
- Excluir um posto/linha da grade
- Aplicar filtro por data
- Recarregar/navegar na página

Sem essa versão, ao excluir um posto ou aplicar filtro, todos os valores digitados nos inputs eram perdidos. Agora eles são salvos automaticamente no navegador e restaurados quando a página recarrega.

## Problemas Resolvidos

### 1. Perda de dados ao excluir um posto
**Antes (v8.10):** Ao clicar no botão "Excluir" para remover uma linha, a página recarregava sem os valores digitados.
**Depois (v8.11):** Os valores são salvos no localStorage antes de excluir, e restaurados automaticamente.

### 2. Perda de dados ao aplicar filtro por data
**Antes (v8.10):** Ao marcar datas e clicar "Filtrar por data(s)", a página recarregava e todas as etiquetas/lacres digitados desapareciam.
**Depois (v8.11):** Os valores são salvos no localStorage antes do filtro, e restaurados para as linhas correspondentes.

## Implementação

### Novas Funções JavaScript (ES5, compatível com PHP 5.3)

#### `salvarEstadoEtiquetasCorreios()`
Percorre todas as linhas da grade de postos (Correios) e salva os valores dos inputs:
- Lacre IIPR
- Lacre Correios
- Etiqueta Correios (35 dígitos)

Armazena em localStorage com chave identificadora baseada em:
- ID do despacho
- Código da regional
- Código do posto

```javascript
// Exemplo de chave localStorage:
// "oficioCorreios:123456:0950:8005"
//                   ↑     ↑     ↑
//            id_despacho regional posto
```

#### `restaurarEstadoEtiquetasCorreios()`
Após recarregar a página ou redesenhar a grade, busca os valores no localStorage e preenche os inputs correspondentes.

Se um posto foi excluído, a função simplesmente não o encontrará na grade, portanto não tentará restaurar (sem erro).

### Ganchos de Chamada

1. **Ao carregar a página** (DOMContentLoaded)
   - `inicializarMonitoramentoAlteracoes()` agora chama `restaurarEstadoEtiquetasCorreios()` primeiro
   - Garante que valores anteriores sejam restaurados ao reabrir a página

2. **Antes de excluir um posto**
   - `excluirPosto()` chama `salvarEstadoEtiquetasCorreios()` antes de submeter o formulário
   - `excluirPostoRegional()` chama `salvarEstadoEtiquetasCorreios()` antes de submeter o formulário

3. **Antes de aplicar filtro por data**
   - Formulário `formFiltroData` tem `onsubmit="salvarEstadoEtiquetasCorreios();"` 
   - Salva estado antes de enviar o filtro

### Compatibilidade

- **JavaScript:** ES5 puro, sem arrow functions, sem `let`/`const`, sem async/await
- **PHP:** Nenhuma alteração no PHP (compatível com PHP 5.3.3)
- **Navegadores:** Funciona em qualquer navegador que suporte localStorage (IE8+)

### Fallback de Segurança

Se localStorage não estiver disponível (navegador privado, desabilitado, etc.):
```javascript
if (typeof window.localStorage === 'undefined') {
    return; // Não salva, mas também não quebra a página
}
```

## Testes Recomendados

### Teste 1: Excluir um Posto
1. Abrir `lacres_novo.php`
2. Preencher valores de lacre IIPR, lacre Correios e etiqueta para alguns postos
3. Clicar "Excluir" em um dos postos
4. Confirmar a exclusão
5. **Esperado:** A página recarrega, a linha é removida, MAS os valores dos outros postos ainda aparecem

### Teste 2: Filtrar por Data
1. Preencher valores nos inputs de lacre/etiqueta
2. Selecionar uma ou mais datas no formulário de filtro
3. Clicar "Filtrar por data(s)"
4. **Esperado:** A página recarrega com a nova grade, e os valores digitados antes do filtro são restaurados

### Teste 3: Recarregar Página
1. Preencher valores nos inputs
2. Pressionar F5 ou clicar no botão "Recarregar" do navegador
3. **Esperado:** Os valores persistem

### Teste 4: Limpar localStorage (Caso Extremo)
1. Abrir DevTools (F12)
2. Console: `localStorage.clear()`
3. Recarregar página (F5)
4. **Esperado:** Inputs vazios (esperado, pois localStorage foi limpo), mas página funciona normalmente

## O Que NÃO Foi Alterado

- Lógica de SPLIT da CENTRAL IIPR (v8.3+)
- Validação de não-duplicidade de etiquetas (CAPITAL + REGIONAIS)
- Comportamento de `prepararLacresCorreiosParaSubmit()` (v8.9+)
- Gravação em ciDespachoLotes (v8.10+)
- Fluxo Poupa Tempo

## Estrutura de Dados localStorage

Exemplo de entrada localStorage para um despacho:

```
Chave: "oficioCorreios:123456:0950:8005"
Valor: {
  "lacre_iipr": "12345",
  "lacre_correios": "67890",
  "etiqueta_correios": "12345678901234567890123456789012345"
}
```

Cada chave é única e específica a um posto/regional/despacho.

## Limpeza Automática (Opcional)

Se necessário, pode ser implementada uma função para limpar localStorage apenas após salvar com sucesso no banco de dados:

```javascript
// Pseudocódigo (não implementado em v8.11)
function limparLocalStorageAposSalvar(idDespacho) {
    for (var i = 0; i < localStorage.length; i++) {
        var chave = localStorage.key(i);
        if (chave.startsWith('oficioCorreios:' + idDespacho + ':')) {
            localStorage.removeItem(chave);
        }
    }
}
```

Isso poderia ser chamado após um `Gravar Dados` bem-sucedido, para evitar acumular muitos itens no localStorage.

## Versões Anteriores

- **v8.10:** Corrigiu salvamento de lacres por regional (normalização de formato)
- **v8.9:** Implementou lacres compartilhados por regional
- **v8.8:** Introduziu captura alinhada de lacres/etiquetas via arrays POST
- **v8.7:** Fallback de lacres para 0 (segurança)
- **v8.0-v8.1:** SPLIT para CENTRAL IIPR

## Próximas Melhorias (Futuro)

- Limpeza automática de localStorage após salvar com sucesso
- Limite de tamanho de localStorage para evitar excesso de dados
- Sincronização entre abas do navegador (não necessário para v8.11)
