# 📚 Guia do Usuário - Versão 9.7.1

## Bem-vindo às Novas Funcionalidades! 🎉

Este guia mostra **como usar** as três novas funcionalidades da versão 9.7.1 do sistema de lacres.

---

## 1️⃣ Como Usar o Filtro de Data por Período

### Cenário: Você precisa conferir ofícios de uma semana específica

**Passo a Passo:**

1. **Localize a seção "🗓️ Filtrar por Período"**
   - Ela fica logo abaixo dos campos "Lacre Capital", "Lacre Central" e "Lacre Regionais"
   - Tem um fundo cinza claro

2. **Preencha as datas:**
   - **Data Inicial:** Digite a data de início (ex: `15/01/2026`)
   - **Data Final:** Digite a data de fim (ex: `21/01/2026`)
   - Formato obrigatório: `dd/mm/aaaa`

3. **Clique em "Aplicar Período"**
   - O botão azul ao lado dos campos
   - A página irá recarregar automaticamente

4. **Resultado:**
   - Apenas postos das datas **entre 15/01 e 21/01** serão exibidos
   - Se não houver dados nesse período, a tela ficará vazia

### 💡 Dica:
- Se você deixar os campos em branco, o sistema usará os checkboxes de data (como antes)
- Você pode combinar: usar o período E marcar checkboxes adicionais

---

## 2️⃣ Como Entender o Indicador de Dias

### Cenário: Você quer saber quais dias já foram conferidos recentemente

**O que você verá:**

No **canto superior direito** da tela, há um painel fixo com duas listas:

```
📅 Status de Conferências

✓ Com Conferência:
20/01/2026, 19/01/2026, 18/01/2026, 17/01/2026, 16/01/2026 (+3 mais)

✗ Sem Conferência:
23/01/2026, 22/01/2026, 21/01/2026, 15/01/2026, 14/01/2026 (+2 mais)
```

### 📖 Como Interpretar:

- **Verde (✓):** Dias em que **houve conferência** (postos foram cadastrados em ciPostosCsv)
- **Vermelho (✗):** Dias em que **não houve conferência** (nenhum dado foi inserido)
- **"(+N mais)":** Há mais datas além das 5 exibidas

### 📌 Importante:
- O sistema verifica os **últimos 30 dias** do calendário
- Atualiza automaticamente toda vez que você recarregar a página
- Se o banco estiver vazio, ambas as seções mostrarão "Nenhum"

### 💼 Caso de Uso Real:

**Situação:** Hoje é 23/01/2026 (segunda-feira)  
**Você vê:**
- ✓ Com Conferência: 19/01, 18/01, 17/01 (quinta, quarta, terça)
- ✗ Sem Conferência: 22/01, 21/01, 20/01 (domingo, sábado, sexta)

**Interpretação:** O fim de semana não teve conferência, mas a semana passada sim.

---

## 3️⃣ Como Usar o Pop-up de Etiquetas

### Cenário: Você está escaneando etiquetas dos Correios com um leitor de código de barras

**Passo a Passo:**

1. **Selecione datas e filtre postos** (usando filtro de período ou checkboxes)

2. **Localize a tabela de postos** (CAPITAL ou REGIONAIS)

3. **Clique no primeiro input de "Etiqueta Correios"**
   - Um **pop-up roxo** aparecerá no centro da tela

4. **O que você verá no pop-up:**
   ```
   🎯 Leitura de Etiqueta
   
   POSTO 042 - CASCAVEL
   
   📦 Escaneie o código de barras da etiqueta (35 dígitos)
   
   Posto 1 de 15
   ```

5. **Escaneie o código de barras:**
   - O contador mudará para: `Posto 1 de 15 • 15/35 dígitos`
   - Quando atingir 35 dígitos, o pop-up fecha automaticamente
   - O sistema valida a etiqueta
   - Se válida, **avança automaticamente** para o próximo posto
   - Novo pop-up abre mostrando o próximo posto

6. **Continue escaneando:**
   - Repita para todos os postos da lista
   - O pop-up sempre mostrará qual posto você está lendo

### ⚡ Recursos Automáticos:

- **Auto-avançamento:** Ao completar 35 dígitos, vai para o próximo
- **Validação de duplicatas:** Se a etiqueta já foi usada, o sistema alerta e limpa o campo
- **Contador em tempo real:** Mostra quantos dígitos já foram lidos
- **Foco visual:** Você sempre sabe qual posto está conferindo

### 🔍 Exemplo Visual:

```
┌──────────────────────────────────────┐
│  🎯 Leitura de Etiqueta              │
│                                      │
│  POSTO 086 - MARECHAL CANDIDO RONDON│
│                                      │
│  📦 Escaneie o código de barras...   │
│                                      │
│  Posto 7 de 23 • 28/35 dígitos      │
└──────────────────────────────────────┘
```

### 🚫 Quando o Pop-up NÃO aparece:

- Inputs da **CENTRAL IIPR** (porque usam lógica diferente)
- Inputs de **Poupa Tempo** (não têm etiquetas Correios)
- Inputs de **Lacre IIPR** ou **Lacre Correios** (são numéricos simples)

---

## 🎯 Fluxo de Trabalho Completo

### Cenário Real: Conferir ofícios da última semana

1. **Verificar dias pendentes:**
   - Olhar o indicador no canto superior direito
   - Identificar dias sem conferência (vermelho)

2. **Filtrar por período:**
   - Data Inicial: `16/01/2026`
   - Data Final: `23/01/2026`
   - Clicar "Aplicar Período"

3. **Preencher lacres iniciais:**
   - Lacre Capital: `12345`
   - Lacre Central: `12400`
   - Lacre Regionais: `12500`

4. **Escanear etiquetas:**
   - Clicar no primeiro input de etiqueta
   - Pop-up abre mostrando "POSTO 001 - ..."
   - Escanear código de barras
   - Pop-up fecha e abre no próximo posto
   - Repetir para todos

5. **Gravar ofício:**
   - Clicar "Gravar e Imprimir Correios"
   - Escolher "Sobrescrever" ou "Criar Novo"
   - Confirmar

6. **Resultado:**
   - PDF gerado com todos os dados
   - Etiquetas salvas no banco
   - Na próxima vez que abrir, o dia aparecerá em verde (com conferência)

---

## ❓ Perguntas Frequentes (FAQ)

### Q: O filtro de período funciona com datas futuras?
**R:** Sim, mas se não houver dados no banco para essas datas, a tela ficará vazia.

### Q: Posso usar o filtro de período e os checkboxes juntos?
**R:** Não recomendado. Se você preencher o período, os checkboxes são ignorados.

### Q: O indicador de dias mostra sábados e domingos?
**R:** Sim, mostra **todos** os dias do calendário (incluindo fins de semana).

### Q: O pop-up funciona com digitação manual?
**R:** Sim! Você pode digitar os 35 dígitos manualmente e o contador funcionará.

### Q: Posso fechar o pop-up antes de terminar?
**R:** Sim, basta clicar fora ou pressionar Tab. O pop-up fecha automaticamente.

### Q: O que acontece se eu escanear a mesma etiqueta duas vezes?
**R:** O sistema detecta duplicatas e exibe um alerta. O campo é limpadoautomaticamente e o foco permanece no mesmo posto.

---

## 🎨 Personalização (Futuras Versões)

### Ideias para melhorias:

- [ ] Escolher quantos dias mostrar no indicador (30, 60, 90)
- [ ] Exportar lista de dias sem conferência para Excel
- [ ] Sons diferentes para etiquetas válidas/inválidas
- [ ] Pop-up com tema claro/escuro
- [ ] Atalhos de teclado (Ctrl+D para abrir filtro de datas)

---

## 📞 Suporte

**Dúvidas ou problemas?**

1. Verifique os [Release Notes](RELEASE_NOTES_v9.7.1.md)
2. Execute o [Guia de Teste](TESTE_v9.7.1.md)
3. Entre em contato com a equipe IIPR

---

## ✅ Checklist de Aprendizado

Você dominou as novas funcionalidades quando conseguir:

- [ ] Filtrar ofícios por um intervalo de 7 dias
- [ ] Identificar no indicador quais dias não foram conferidos
- [ ] Escanear 10 etiquetas seguidas usando o pop-up
- [ ] Explicar para um colega como usar o filtro de período

---

**Versão do Guia:** 1.0  
**Data:** 23/01/2026  
**Sistema:** lacres_novo.php v9.7.1
