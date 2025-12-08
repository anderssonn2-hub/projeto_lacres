# Versão 8.9 - Lacres/Etiqueta por Regional

## Resumo da mudança

Na v8.9, implementamos suporte a lacres compartilhados por **regional**. Todos os lotes/postos de uma regional agora podem receber os mesmos lacres IIPR, lacre Correios e etiqueta Correios com um único cadastro.

## Problema resolvido

Anteriormente (v8.8):
- Quando você cadastrava lacres em uma linha de posto, apenas aquele posto específico recebia os valores.
- Os demais postos da mesma regional (ex: 950) ficavam com valores zerados/NULL.

Agora (v8.9):
- Você cadastra lacres em **qualquer linha de uma regional**.
- Todos os lotes daquele regional que estejam sendo gravados recebem o **mesmo lacre/etiqueta**.
- Se um posto específico tiver lacres próprios, esse lacre é usado; caso contrário, usa o da regional.

## Mudanças técnicas

### 1. Frontend (JavaScript)

**Arquivo:** `lacres_novo.php` (função `prepararLacresCorreiosParaSubmit`)

- Estendido para capturar a regional de cada linha via `data-regional-codigo`.
- Cria agora um array adicional: `regional_lacres[]` alinhado com `posto_lacres[]`.
- Ao submeter, envia também a regional de cada linha junto com os lacres.

**HTML das linhas de Correios (tabela de lacres)**

- Adicionado atributo `data-regional-codigo` em cada `<tr>` da tabela para armazenar o código regional.

### 2. Backend (PHP - handler `salvar_oficio_correios`)

**Arquivo:** `lacres_novo.php` (linhas ~365+)

#### a) Query SQL de lotes (`sqlLotes`)
- Agora inclui `r.regional` no SELECT para cada lote.
- Permite que o backend conheça a regional de cada lote sendo gravado.

#### b) Novo mapa: `$mapaLacresPorRegional`
- Complementa o `$mapaLacresPorPosto` já existente.
- Construído a partir dos arrays alinhados enviados pelo formulário (`regional_lacres[]`, etc.).
- Formato: `$mapaLacresPorRegional['950'] = ['lacre_iipr' => 111, 'lacre_correios' => 222, 'etiqueta_correios' => 'ETQ...']`

#### c) Lógica de prioridade no INSERT de `ciDespachoLotes`
```
1º: Se o posto tem lacre específico em $mapaLacresPorPosto → use esse
2º: Se não, e a regional tem lacre em $mapaLacresPorRegional → use o da regional
3º: Se nenhum dos anteriores → use defaults (0, 0, NULL)
```

#### d) Debug adicionado
- `add_debug('V8.9 - MAPA DE LACRES POR REGIONAL', $mapaLacresPorRegional);`
- Acessível via `?debug=1` na URL para inspecionar os mapas criados.

## Como usar

### Caso de uso: Todos os postos da regional 950 compartilham lacre

1. Abra `lacres_novo.php`.
2. Procure a seção **REGIONAIS** na tabela.
3. Encontre qualquer linha da regional 950 (ex: posto 976, 977, etc.).
4. Preencha:
   - **Lacre IIPR:** 111
   - **Lacre Correios:** 222
   - **Etiqueta Correios:** ABC123...
5. Clique em "Gravar Dados".
6. Na query `consulta_producao.php`, busque qualquer lote de qualquer posto da regional 950.
   - Você encontrará lacres 111, 222 e etiqueta ABC123 em **todos** os lotes dessa regional.

### Caso de uso: Um posto específico tem lacre diferente

1. Se você quiser que o posto 050 (também na regional 950) tenha lacres próprios:
   - Preencha lacres na linha **específica** do posto 050.
   - Esses lacres têm **prioridade** sobre os lacres da regional.
   - Os demais postos da regional continuarão com os lacres regionais.

## Compatibilidade

- ✅ Mantém v8.8 funcionando (suporte a lacres por posto ainda existe).
- ✅ Não quebra SPLIT, validação de etiquetas, auto-foco.
- ✅ Não quebra lógica do Poupa Tempo (ciDespachoItens).
- ✅ Nenhuma migração de banco de dados necessária.

## Testes recomendados

1. **Teste básico:** Preencha lacre em uma linha de regional → verifique todos os lotes da regional.
2. **Teste de prioridade:** Preencha lacre na regional E em um posto específico → verifique que o posto recebe o lacre específico, não o regional.
3. **Teste de debug:** Use `?debug=1` para inspecionar `V8.9 - MAPA DE LACRES POR REGIONAL`.
4. **Teste de compatibilidade:** Verifique que o SPLIT, validação de etiquetas e Poupa Tempo ainda funcionam.

## Mudanças em arquivos

- `lacres_novo.php`: Versão atualizada para v8.9 com suporte a regional nos lacres.
- Nenhum outro arquivo foi alterado (compatível com v8.8).

---

**Versão:** 8.9  
**Data:** 2025-12-03  
**Mudanças:** Lacres por regional, prioridade regional/posto, debug v8.9  
**Status:** Pronto para testar
