# Release Notes - Versão 8.14.6

## 📋 Resumo

Versão 8.14.6 integra o salvamento de etiquetas dos Correios ao botão "Gravar e Imprimir Correios", eliminando a necessidade de clicar em dois botões separados e oferecendo controle inteligente sobre etiquetas já gravadas anteriormente.

**Data de Lançamento:** 9 de Dezembro de 2025  
**Compatibilidade:** Mantém 100% das funcionalidades anteriores (v8.14.5 e anteriores)

---

## 🎯 Problema Resolvido

### ❌ Antes (v8.14.5)

Para salvar um ofício dos Correios completamente, o usuário precisava:

1. ✅ Clicar em "**Gravar e Imprimir Correios**" → salvava ofício em `ciDespachos` + `ciDespachoLotes`
2. ✅ Clicar em "**Salvar Etiquetas Correios**" → salvava etiquetas em `ciMalotes`

**Problemas:**
- Dois cliques necessários (fluxo fragmentado)
- Risco de esquecer de salvar etiquetas
- Sem controle de etiquetas duplicadas ao regravar

### ✅ Depois (v8.14.6)

Um único clique em "**Gravar e Imprimir Correios**" faz TUDO:

1. ✅ Modal pergunta: "Como gravar ofício?" (Sobrescrever / Criar Novo / Cancelar)
2. ✅ Modal pergunta: "Salvar X etiquetas?" (Sobrescrever / Manter Anteriores / Não Salvar)
3. ✅ Salva ofício + etiquetas simultaneamente
4. ✅ Imprime automaticamente

**Benefícios:**
- ✅ **Fluxo unificado** (um único botão)
- ✅ **Controle inteligente** de etiquetas duplicadas
- ✅ **Opções flexíveis** (sobrescrever vs. manter)
- ✅ **Rastreabilidade** (login do responsável gravado)
- ✅ **Botão separado** "Salvar Etiquetas Correios" mantido para uso independente

---

## 🔧 Mudanças Técnicas

### 1. Novo Handler PHP: `salvar_oficio_e_etiquetas_correios`

**Arquivo:** `lacres_novo.php` (linhas ~1102-1238)

Combina duas operações em uma transação:

```php
if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_oficio_e_etiquetas_correios') {
    // ETAPA 1: Salvar ofício (reutiliza handler salvar_oficio_correios)
    // ETAPA 2: Salvar etiquetas em ciMalotes
    
    // Modo sobrescrever: DELETE etiquetas anteriores das mesmas datas
    if ($modo_etiquetas === 'sobrescrever') {
        DELETE FROM ciMalotes WHERE data IN (...)
    }
    
    // INSERT etiquetas com CEP, sequencial, login
    INSERT INTO ciMalotes (leitura, data, observacao, login, tipo, cep, sequencial, posto)
}
```

**Parâmetros:**
- `modo_etiquetas`: `'sobrescrever'` | `'novo'` | `'nao_salvar'`
- `login_etiquetas`: Nome do responsável (gravado em `ciMalotes.login`)
- `correios_datas`: Datas do ofício (para DELETE seletivo)

---

### 2. Modal de Confirmação em Duas Etapas

**Arquivo:** `lacres_novo.php` (linhas ~4256-4389)

#### Etapa 1: Modal de Ofício

Função: `confirmarGravarEImprimir()`

```javascript
// 3 opções para ofício
- Sobrescrever: Apaga lotes do último ofício
- Criar Novo: Mantém ofício anterior
- Cancelar: Aborta operação
```

Ao escolher Sobrescrever ou Criar Novo → chama `modalEtiquetasCorreios()`

#### Etapa 2: Modal de Etiquetas

Função: `modalEtiquetasCorreios(modoOficio)`

```javascript
// Conta etiquetas válidas (35 dígitos)
var etiquetasValidas = contarEtiquetasValidas();

if (etiquetasValidas === 0) {
    // Pula para gravação sem etiquetas
    gravarEImprimirCorreiosComEtiquetas('nao_salvar', modoOficio);
    return;
}

// 3 opções para etiquetas
- Sobrescrever: DELETE etiquetas antigas + INSERT novas
- Manter Anteriores: apenas INSERT novas
- Não Salvar: grava ofício sem tocar em ciMalotes
```

**Interface do Modal:**
- 📦 Contador de etiquetas válidas
- 📝 Campo para nome do responsável (pré-preenchido)
- 🎨 Botões coloridos (laranja/verde/cinza)

---

### 3. Função de Salvamento Unificada

**Arquivo:** `lacres_novo.php` (linhas ~4520-4560)

Função: `gravarEImprimirCorreiosComEtiquetas(modoEtiquetas, modoOficio, loginEtiquetas)`

```javascript
function gravarEImprimirCorreiosComEtiquetas(modoEtiquetas, modoOficio, loginEtiquetas) {
    var form = document.getElementById('formOficioCorreios');
    
    // Preencher inputs visualmente
    preencherInputsParaImpressao();
    
    // Salvar estado no localStorage
    salvarEstadoEtiquetasCorreios();
    
    if (modoEtiquetas === 'nao_salvar') {
        // Ação antiga (sem etiquetas)
        document.getElementById('acaoCorreios').value = 'salvar_oficio_correios';
    } else {
        // Ação nova (com etiquetas)
        document.getElementById('acaoCorreios').value = 'salvar_oficio_e_etiquetas_correios';
        
        // Adicionar campos hidden
        <input name="modo_etiquetas" value="...">
        <input name="login_etiquetas" value="...">
    }
    
    form.submit();
}
```

---

## 📊 Fluxo de Dados

```
┌─────────────────────────────────────────────────────────┐
│  Usuário clica "Gravar e Imprimir Correios"             │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│  Modal 1: Como gravar ofício?                            │
│  [ Sobrescrever ] [ Criar Novo ] [ Cancelar ]           │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│  JavaScript conta etiquetas válidas                      │
│  contarEtiquetasValidas() → retorna N                    │
└─────────────────────────────────────────────────────────┘
                          ↓
           ┌──────────────┴──────────────┐
           │                             │
      N = 0                           N > 0
           │                             │
           ↓                             ↓
  ┌─────────────────┐         ┌──────────────────────────┐
  │ Pula etiquetas  │         │ Modal 2: Salvar X etiquetas? │
  │ Grava só ofício │         │ Campo: Login do responsável  │
  └─────────────────┘         │ [ Sobrescrever ]             │
                              │ [ Manter Anteriores ]        │
                              │ [ Não Salvar ]               │
                              └──────────────────────────┘
                                          ↓
           ┌──────────────┬───────────────┼───────────────┐
           │              │               │               │
     Sobrescrever    Manter Anteriores  Não Salvar  Cancelar
           │              │               │
           ↓              ↓               ↓
  ┌────────────────┐ ┌────────────┐ ┌────────────┐
  │ POST:          │ │ POST:      │ │ POST:      │
  │ modo_etiquetas │ │ modo_etiq  │ │ acao =     │
  │ = sobrescrever │ │ = novo     │ │ salvar_    │
  │                │ │            │ │ oficio_    │
  │ login_etiquetas│ │ login_etiq │ │ correios   │
  │ = "João"       │ │ = "Maria"  │ │            │
  └────────────────┘ └────────────┘ └────────────┘
           │              │               │
           └──────────────┴───────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│  PHP Handler: salvar_oficio_e_etiquetas_correios        │
│  ou salvar_oficio_correios (sem etiquetas)              │
└─────────────────────────────────────────────────────────┘
                          ↓
           ┌──────────────┴──────────────┐
           │                             │
    Com Etiquetas                   Sem Etiquetas
           │                             │
           ↓                             ↓
  ┌─────────────────┐         ┌─────────────────┐
  │ 1. Salvar ofício│         │ 1. Salvar ofício│
  │    ciDespachos  │         │    ciDespachos  │
  │    ciDespacho   │         │    ciDespacho   │
  │    Lotes        │         │    Lotes        │
  │                 │         └─────────────────┘
  │ 2. Modo sobres? │                  ↓
  │    DELETE       │         ┌─────────────────┐
  │    ciMalotes    │         │ FIM: Redirect   │
  │    (datas)      │         │ + Imprimir      │
  │                 │         └─────────────────┘
  │ 3. INSERT       │
  │    ciMalotes    │
  │    (etiquetas)  │
  └─────────────────┘
           ↓
┌─────────────────────────────────────────────────────────┐
│  Mensagem: Ofício salvo! X etiquetas salvas por João    │
└─────────────────────────────────────────────────────────┘
           ↓
┌─────────────────────────────────────────────────────────┐
│  window.print() + Redirect                               │
└─────────────────────────────────────────────────────────┘
```

---

## 🧪 Como Testar

### Teste 1: Salvamento Básico (Novo Ofício + Etiquetas Novas)

1. Abrir `lacres_novo.php`
2. Preencher lacres e **3 etiquetas** válidas (35 dígitos)
3. Clicar "**💾🖨️ Gravar e Imprimir Correios**"
4. Modal 1 aparece → escolher "**Criar Novo**"
5. Modal 2 aparece → confirmar "**Manter Anteriores**"
6. Preencher nome do responsável (ex: "João")
7. Confirmar

**Resultado Esperado:**
```
✅ Alert: "Ofício Correios salvo com sucesso!
           ✓ 3 etiquetas salvas em ciMalotes por João."
✅ Impressão automática (window.print)
✅ Redirect para página com datas preservadas
```

---

### Teste 2: Sobrescrever Etiquetas (Segunda Gravação)

1. Alterar 2 etiquetas na tela (manter 1 igual)
2. Clicar "**Gravar e Imprimir Correios**"
3. Modal 1 → escolher "**Sobrescrever**"
4. Modal 2 → escolher "**Sobrescrever**"
5. Nome: "Maria"

**Resultado Esperado:**
```
✅ Etiquetas antigas das mesmas datas são DELETADAS
✅ 3 novas etiquetas são INSERT
✅ ciMalotes.login = 'Maria'
✅ ciMalotes.observacao = 'Salva via Gravar+Imprimir por Maria em 09/12/2025'
```

**Verificar no banco:**
```sql
SELECT * FROM ciMalotes WHERE data = '2025-12-09' ORDER BY id DESC;
-- Deve mostrar apenas as 3 últimas etiquetas (antigas foram deletadas)
```

---

### Teste 3: Não Salvar Etiquetas (Apenas Ofício)

1. Preencher etiquetas na tela
2. Clicar "**Gravar e Imprimir Correios**"
3. Modal 1 → escolher "**Criar Novo**"
4. Modal 2 → escolher "**Não Salvar**"

**Resultado Esperado:**
```
✅ Ofício salvo em ciDespachos + ciDespachoLotes
❌ Nenhum INSERT em ciMalotes
✅ Etiquetas continuam na tela (não são perdidas)
```

---

### Teste 4: Sem Etiquetas na Tela

1. Zerar todos os campos de etiqueta
2. Clicar "**Gravar e Imprimir Correios**"
3. Modal 1 → escolher "**Criar Novo**"

**Resultado Esperado:**
```
✅ Modal 2 NÃO aparece (pula automaticamente)
✅ Ofício salvo normalmente
✅ Alert: "Ofício Correios salvo com sucesso!
           ⚠ Nenhuma etiqueta válida encontrada para salvar."
```

---

### Teste 5: Botão Separado "Salvar Etiquetas Correios"

1. Clicar no botão "**💾 Salvar Etiquetas Correios**" (separado)
2. Modal antigo aparece (sem perguntar sobre ofício)
3. Confirmar nome do responsável
4. Salvar

**Resultado Esperado:**
```
✅ Funciona independentemente (v8.14.5 mantido)
✅ Salva APENAS etiquetas em ciMalotes
✅ NÃO toca em ciDespachos / ciDespachoLotes
```

---

## 📋 Checklist de Validação

### Funcionalidades Novas (v8.14.6)

- [ ] Modal duplo aparece ao clicar "Gravar e Imprimir Correios"
- [ ] Contador de etiquetas válidas funciona
- [ ] Campo de login pré-preenchido com responsável
- [ ] Modo sobrescrever DELETE etiquetas antigas
- [ ] Modo manter anteriores apenas INSERT novas
- [ ] Modo não salvar pula etiquetas
- [ ] Login gravado em ciMalotes.login
- [ ] Observação inclui nome do responsável e data
- [ ] Mensagem mostra quantidade de etiquetas salvas
- [ ] CENTRAL IIPR não duplica etiquetas

### Compatibilidade com Versões Anteriores

- [ ] Botão "Salvar Etiquetas Correios" separado funciona
- [ ] Poupa Tempo não quebrou (modelo_oficio_poupa_tempo.php)
- [ ] Modal PT continua funcionando (v8.14.5)
- [ ] Botões pulsantes funcionam (v8.14.5)
- [ ] Validação FK mantida (v8.14.5)
- [ ] Lotes salvos corretamente (v8.14.4)
- [ ] localStorage preserva etiquetas (v8.11)
- [ ] SPLIT CENTRAL IIPR funciona (v8.3)

---

## 📊 Comparação de Versões

| Recurso | v8.14.5 | v8.14.6 |
|---------|---------|---------|
| Gravar Ofício Correios | ✅ | ✅ |
| Imprimir Automaticamente | ✅ | ✅ |
| Salvar Etiquetas (separado) | ✅ | ✅ |
| **Salvar Etiquetas (integrado)** | ❌ | ✅ |
| **Modal duplo (ofício + etiquetas)** | ❌ | ✅ |
| **Sobrescrever etiquetas antigas** | ❌ | ✅ |
| **Login do responsável gravado** | ⚠️ Manual | ✅ Auto |
| Modal PT | ✅ | ✅ |
| Botões Pulsantes | ✅ | ✅ |
| Validação FK | ✅ | ✅ |

---

## 🐛 Problemas Conhecidos Resolvidos

### v8.14.6

1. ✅ **Fluxo fragmentado corrigido**  
   Antes: 2 cliques (ofício + etiquetas)  
   Depois: 1 clique com opções inteligentes

2. ✅ **Duplicação de etiquetas controlada**  
   Antes: Não havia controle ao regravar  
   Depois: Opção explícita (sobrescrever vs. manter)

3. ✅ **Rastreabilidade melhorada**  
   Antes: Login manual no campo  
   Depois: Login capturado no modal e gravado automaticamente

4. ✅ **CENTRAL IIPR sem duplicatas**  
   Usa `$etiquetas_central_salvas` para evitar INSERT duplicado

---

## 🚀 Compatibilidade

- ✅ PHP 5.3.3+ (Yii 1.x)
- ✅ JavaScript ES5 (sem arrow functions, sem let/const)
- ✅ MySQL 5.5+
- ✅ Navegadores: IE9+, Chrome, Firefox, Edge, Safari

**Tabelas do Banco:**
- `ciDespachos` (ofícios)
- `ciDespachoLotes` (lotes por posto)
- `ciMalotes` (etiquetas dos Correios) ← **NOVO USO em v8.14.6**

**Colunas ciMalotes:**
```sql
leitura VARCHAR(35)       -- Etiqueta completa (35 dígitos)
data DATE                 -- Data do salvamento
observacao TEXT           -- "Salva via Gravar+Imprimir por João em 09/12/2025"
login VARCHAR(50)         -- Nome do responsável
tipo INT                  -- Tipo padrão = 1
cep VARCHAR(8)            -- Primeiros 8 dígitos da etiqueta
sequencial VARCHAR(5)     -- Últimos 5 dígitos da etiqueta
posto VARCHAR(10)         -- Código do posto (ex: '050')
```

---

## 📝 Notas Importantes

### 1. Modo Sobrescrever vs. Manter Anteriores

**Sobrescrever:**
```sql
-- Deleta etiquetas das mesmas datas do ofício
DELETE FROM ciMalotes WHERE data IN ('2025-12-08', '2025-12-09');
-- Depois insere as novas
INSERT INTO ciMalotes ...
```

**Manter Anteriores:**
```sql
-- Apenas insere as novas (não deleta nada)
INSERT INTO ciMalotes ...
```

**Recomendação:**
- Use **Sobrescrever** quando corrigir etiquetas erradas
- Use **Manter Anteriores** quando adicionar etiquetas complementares

---

### 2. CENTRAL IIPR - Controle de Duplicatas

A CENTRAL IIPR compartilha a mesma etiqueta entre múltiplos postos. O código evita INSERT duplicado:

```php
$etiquetas_central_salvas = array();

foreach ($_SESSION['etiquetas'] as $posto_codigo => $etiqueta) {
    if (in_array($posto_codigo, $CENTRAL)) {
        if (in_array($etiqueta, $etiquetas_central_salvas)) {
            continue; // Pula duplicata
        }
        $etiquetas_central_salvas[] = $etiqueta;
    }
    
    INSERT INTO ciMalotes ...
}
```

Resultado: Mesmo que 5 postos da CENTRAL tenham etiqueta "123...", apenas 1 INSERT é feito.

---

### 3. Botão Separado Mantido

O botão **"💾 Salvar Etiquetas Correios"** continua disponível para:

- Salvar apenas etiquetas sem gravar ofício
- Usuários que preferem fluxo antigo (2 cliques)
- Situações onde ofício já foi salvo mas etiquetas não

**Não há conflito:** Ambos os botões funcionam independentemente.

---

### 4. localStorage Preservado

Todas as funcionalidades de localStorage (v8.11) continuam funcionando:

- ✅ Etiquetas preservadas ao excluir posto
- ✅ Etiquetas preservadas ao filtrar por data
- ✅ Etiquetas restauradas ao recarregar página

---

## 🔜 Próximas Melhorias (Futuro)

### v8.14.7 (Sugerido)

- Adicionar relatório de etiquetas salvas por período
- Exportar etiquetas para CSV
- Auditoria: histórico de sobrescritas

### v8.15.0 (Futuro)

- Dashboard com estatísticas de ofícios + etiquetas
- Gráficos de etiquetas por regional
- Alertas de etiquetas duplicadas no banco

---

## 📚 Referências

### Código-fonte

- `lacres_novo.php` linhas 91-108: Header v8.14.6
- `lacres_novo.php` linhas 1102-1238: Handler `salvar_oficio_e_etiquetas_correios`
- `lacres_novo.php` linhas 4256-4389: Modais de confirmação
- `lacres_novo.php` linhas 4520-4560: Função `gravarEImprimirCorreiosComEtiquetas`

### Documentação Anterior

- `RELEASE_NOTES_v8.14.5.md` - Modal PT + Pulsing + FK
- `RELEASE_NOTES_v8.11.md` - localStorage
- `RELEASE_NOTES_v8.9.md` - Lacres por regional

---

**Versão:** 8.14.6  
**Data:** 9 de Dezembro de 2025  
**Status:** ✅ Pronto para Teste  
**Autor:** Sistema de Lacres e Ofícios - CELEPAR  
**Compatibilidade:** Mantém 100% das funcionalidades anteriores

---

## ✨ Conclusão

A versão 8.14.6 unifica o fluxo de salvamento de ofícios e etiquetas dos Correios, oferecendo:

- **Simplicidade:** Um único clique para tudo
- **Controle:** Opções explícitas sobre etiquetas
- **Rastreabilidade:** Login gravado automaticamente
- **Inteligência:** Detecta etiquetas válidas e age conforme necessário
- **Compatibilidade:** Não quebra nenhuma funcionalidade anterior

**Pronto para produção após validação em ambiente de teste!** 🚀
