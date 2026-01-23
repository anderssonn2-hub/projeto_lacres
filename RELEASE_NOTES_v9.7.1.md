# Release Notes - Versão 9.7.1

**Data de Lançamento:** 23 de Janeiro de 2026  
**Arquivo:** `lacres_novo.php`

## 🎯 Visão Geral

A versão 9.7.1 traz melhorias significativas na experiência do usuário (UX) com foco em **filtros avançados de data**, **indicadores visuais de status** e **interface aprimorada para leitura de etiquetas**.

---

## ✨ Novas Funcionalidades

### 1. 📅 Filtros de Data por Período

**Localização:** Formulário de filtro principal (abaixo dos campos Lacre Capital/Central/Regionais)

**Funcionalidade:**
- Dois novos campos de input: **Data Inicial** e **Data Final**
- Formato aceito: `dd/mm/aaaa` (ex: 20/01/2026)
- Botão dedicado "Aplicar Período" para executar o filtro

**Como usar:**
1. Preencha os campos "Data Inicial" e "Data Final" com datas válidas
2. Clique em "Aplicar Período"
3. O sistema buscará automaticamente todas as datas disponíveis no intervalo em `ciPostosCsv`
4. A sessão será atualizada com as datas filtradas

**Comportamento:**
- Se os campos forem deixados em branco, o sistema utiliza a seleção manual de checkboxes (comportamento anterior)
- Query otimizada: `SELECT DISTINCT DATE(dataCarga) ... WHERE DATE(dataCarga) BETWEEN ? AND ?`
- Validação de formato com fallback para modo padrão

---

### 2. 📊 Indicador de Dias com/sem Conferência

**Localização:** Canto superior direito da tela (fixo)

**Funcionalidade:**
- **Painel fixo** mostrando status de conferências dos últimos 30 dias
- Dividido em duas seções:
  - ✅ **Com Conferência**: Últimos 15 dias que possuem dados em `ciPostosCsv`
  - ❌ **Sem Conferência**: Dias do calendário (últimos 30) que NÃO possuem dados
  
**Características:**
- Exibe até 5 datas por seção, com indicação de "mais" quando houver
- Cores diferenciadas:
  - Verde (#28a745) para dias conferidos
  - Vermelho (#dc3545) para dias sem conferência
- Atualização automática a cada carregamento da página
- Estilo moderno com sombra e bordas arredondadas

**Query SQL:**
```sql
SELECT DISTINCT DATE(dataCarga) as data 
FROM ciPostosCsv 
WHERE dataCarga >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY data DESC
LIMIT 15
```

---

### 3. 🎯 Pop-up Centralizado para Etiquetas

**Localização:** Centro da tela (overlay modal)

**Funcionalidade:**
- **Pop-up visual** que aparece automaticamente ao focar em um input de etiqueta Correios
- Mostra claramente:
  - 📦 Nome do posto atual
  - 🎯 Posição na sequência (ex: "Posto 5 de 23")
  - 📊 Progresso da leitura em tempo real (ex: "15/35 dígitos")
  
**Características visuais:**
- Design moderno com gradiente roxo (#667eea → #764ba2)
- Animação suave de entrada (slide + fade)
- Fonte grande e legível para identificação rápida
- Oculta automaticamente ao perder foco (`blur`)

**Eventos:**
- `focus`: Mostra o pop-up e atualiza informações do posto
- `input`: Atualiza contador de dígitos digitados/escaneados
- `blur`: Oculta o pop-up automaticamente

**Compatibilidade:**
- Funciona com scanners de código de barras
- Mantém o comportamento de auto-avançamento (35 dígitos → próximo posto)
- JavaScript puro (ES5) - compatível com navegadores antigos

---

## 🔧 Melhorias Técnicas

### Backend (PHP)

1. **Processamento de Intervalo de Datas**
   ```php
   if (isset($_GET['data_inicial']) && isset($_GET['data_final'])) {
       $data_inicial_sql = DateTime::createFromFormat('d/m/Y', $data_inicial);
       $data_final_sql = DateTime::createFromFormat('d/m/Y', $data_final);
       // Busca datas no intervalo...
   }
   ```

2. **Query de Dias com Conferência**
   - Busca os últimos 30 dias com dados
   - Calcula diferença com calendário completo
   - Armazena em arrays `$dias_com_conferencia` e `$dias_sem_conferencia`

### Frontend (JavaScript)

1. **Funções Globais Adicionadas**
   - `window.mostrarPopupEtiqueta(inputAtual)`: Exibe pop-up com dados do posto
   - `window.ocultarPopupEtiqueta()`: Esconde pop-up
   - `window.atualizarProgressoPopup(digitosLidos)`: Atualiza contador em tempo real

2. **Event Listeners Aprimorados**
   - `focus` → Mostra pop-up
   - `input` → Atualiza contador + dispara blur em 35 dígitos
   - `blur` → Valida duplicatas + oculta pop-up + avança para próximo

---

## 🎨 CSS Adicionado

```css
#popup-etiqueta-focal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    z-index: 10001;
    /* ... animações e estilo moderno */
}
```

**Animação:**
```css
@keyframes popup-appear {
    from { opacity: 0; transform: translate(-50%, -45%); }
    to { opacity: 1; transform: translate(-50%, -50%); }
}
```

---

## 📋 Alterações no Header

```php
/* lacres_novo.php — Versão 9.7.1
 * Sistema de criação e gestão de ofícios (Poupa Tempo e Correios)
 * 
 * CHANGELOG v9.7.1 (23/01/2026):
 * - [NOVO] Filtros de data com inputs para data inicial e data final
 * - [NOVO] Indicador no topo direito mostrando últimos dias com conferência e dias sem conferência
 * - [NOVO] Pop-up centralizado ao clicar em inputs de etiquetas Correios (mostra posto atual)
 * - [NOVO] Melhoria UX: foco visual no posto atual durante leitura de etiquetas
 * - [NOVO] Query otimizada para buscar dias com/sem conferência nos últimos 30 dias
 * - [MANTIDO] Auto-avançamento entre postos após leitura de etiqueta
 * - Compatibilidade: PHP 5.3.3 + ES5 JavaScript
```

---

## ✅ Compatibilidade

- **PHP:** 5.3.3+ (testado com sintaxe procedural)
- **JavaScript:** ES5 (sem let/const/arrow functions)
- **Navegadores:** 
  - Chrome/Edge 90+
  - Firefox 88+
  - Safari 14+
  - IE11 (com polyfills básicos)
  
---

## 📸 Screenshots Sugeridos

1. **Filtro de Data por Período**
   - Mostrar os dois inputs (Data Inicial/Final) + botão
   
2. **Indicador de Dias**
   - Panel fixo no canto superior direito
   
3. **Pop-up de Etiquetas**
   - Modal centralizado durante leitura
   - Contador de dígitos em tempo real

---

## 🚀 Como Atualizar

1. Fazer backup do `lacres_novo.php` atual
2. Substituir pelo novo arquivo versão 9.7.1
3. Limpar cache do navegador (Ctrl+Shift+Del)
4. Testar fluxo completo:
   - Filtrar por período de datas
   - Verificar indicador de dias
   - Escanear etiquetas e observar pop-up

---

## 🐛 Correções de Bugs

Nenhum bug específico corrigido nesta versão (apenas melhorias).

---

## 📝 Notas Adicionais

- A versão exibida no painel "Análise de Expedição" foi atualizada para **v9.7.1**
- A versão exibida no canto superior esquerdo foi atualizada para **9.7.1**
- O indicador de auto-save foi movido para posição `top: 200px` para não sobrepor o indicador de dias

---

## 👨‍💻 Desenvolvido por

**Sistema IIPR - CELEPAR**  
Data de Release: 23/01/2026  
Versão: 9.7.1
