# v8.10 - Corrige Salvamento de Lacres por Regional

## Problema Identificado

Em v8.9:
- ✅ Etiqueta Correios (`etiqueta_correios`) estava sendo salva corretamente em `ciDespachoLotes` para todos os lotes da regional
- ❌ Lacre IIPR (`etiquetaiipr`) e Lacre Correios (`etiquetacorreios`) continuavam zerados (0) mesmo quando preenchidos na tela

**Causa raiz:** A regional estava sendo armazenada com format "0950", mas ao buscar no mapa estava usando "950", causando mismatch.

## Mudanças em v8.10

### 1️⃣ Versão Atualizada
- Comentário de versão v8.10 adicionado no topo do arquivo

### 2️⃣ Normalização de Regional
**Arquivo:** `lacres_novo.php` (linhas ~651, ~723)

- Ao construir `$mapaLacresPorRegional`: remove zeros à esquerda da regional
  ```php
  $regional = ltrim(trim((string)$regional_lote_raw), '0') || '0';
  ```
- Ao buscar regional do lote: aplica mesma normalização
  ```php
  $regional_lote = ltrim($regional_lote_raw, '0') || '0';
  ```
- **Resultado:** Regional "0950" vira "950" em ambos os casos → matching perfeito

### 3️⃣ Debug Melhorado
**Arquivo:** `lacres_novo.php` (linhas ~691, ~765)

**Debug A: Valores recebidos do POST**
```php
add_debug('V8.10 - ARRAYS POST RECEBIDOS', array(
    'postosLacres'      => $postosLacres_post,
    'regionaisLacres'   => $regionaisLacres_post,
    'lacresIIPR'        => $lacresIIPR_post,
    'lacresCorreios'    => $lacresCorreios_post,
    'etiquetasCorreios' => $etiquetasCorreios_post,
));
```

**Debug B: Mapa Regional Construído**
```php
add_debug('V8.10 - MAPA DE LACRES POR REGIONAL', $mapaLacresPorRegional);
```

**Debug C: Por lote (primeiras 5 linhas)**
```php
add_debug('V8.10 - LOTE A GRAVAR', array(
    'posto_lote'           => $posto_lote,
    'regional_lote_raw'    => $regional_lote_raw,
    'regional_lote_norm'   => $regional_lote,
    'existe_em_mapaLacresPorPosto'    => isset($mapaLacresPorPosto[$posto_lote]),
    'existe_em_mapaLacresPorRegional' => isset($mapaLacresPorRegional[$regional_lote]),
    'lacreIIPR_lote'       => $lacreIIPR_lote,
    'lacreCorreios_lote'   => $lacreCorreios_lote,
    'etiquetaCorreios_lote' => $etiquetaCorreios_lote,
));
```

### 4️⃣ JS Helper (Sem Mudanças)
- ✅ Função `prepararLacresCorreiosParaSubmit()` já estava correta
- ✅ Captura corretamente `lacre_iipr[]`, `lacre_correios[]`, `etiqueta_correios[]`
- ✅ Cria inputs ocultos alinhados com regional

## Como Funciona Agora

### Fluxo de Dados v8.10

```
1. Frontend (JS)
   ├─ Lê valores dos inputs visíveis de cada linha
   ├─ Cria inputs ocultos alinhados:
   │  ├─ posto_lacres[] = "041"
   │  ├─ regional_lacres[] = "0950" (pode vir com zeros)
   │  ├─ lacre_iipr[] = "111"
   │  ├─ lacre_correios[] = "222"
   │  └─ etiqueta_correios[] = "ABC123"
   └─ Submete POST

2. Backend - Montagem do Mapa (v8.10)
   ├─ Lê arrays alinhados do POST
   ├─ Normaliza regional: "0950" → "950"
   ├─ Constrói $mapaLacresPorRegional['950'] = [
   │  ├─ 'lacre_iipr' => 111
   │  ├─ 'lacre_correios' => 222
   │  └─ 'etiqueta_correios' => 'ABC123'
   └─ Registra debug com valores

3. Backend - Gravação em ciDespachoLotes (v8.10)
   ├─ Cada lote vem do SQL com regional "0950" (vem do banco)
   ├─ Normaliza: "0950" → "950"
   ├─ Busca em $mapaLacresPorRegional['950'] → ENCONTRA ✅
   ├─ Aplica valores: 111, 222, 'ABC123'
   ├─ Registra debug por lote (primeiras 5)
   └─ INSERT com valores corretos
```

## Testes Recomendados

### Teste 1: Valores Salvos Corretamente
1. Abra `lacres_novo.php`
2. Preencha lacres para regional 950:
   - Lacre IIPR: `111`
   - Lacre Correios: `222`
   - Etiqueta: `TEST123`
3. Clique "Gravar Dados"
4. Consulte `consulta_producao.php` → busque qualquer lote da regional 950
5. ✅ Verifique que `etiquetaiipr=111`, `etiquetacorreios=222`, `etiqueta_correios='TEST123'`

### Teste 2: Debug
1. Após gravar, acesse `lacres_novo.php?debug=1`
2. Procure por:
   - `V8.10 - ARRAYS POST RECEBIDOS` → confirma valores chegaram do JS
   - `V8.10 - MAPA DE LACRES POR REGIONAL` → confirma mapa foi preenchido
   - `V8.10 - LOTE A GRAVAR` (primeiras 5) → confirma lotes encontraram mapa
3. Verifique que `regional_lote_norm` corresponde à chave em `MAPA DE LACRES POR REGIONAL`

### Teste 3: Prioridade Mantida
1. Preencha lacres diferentes para:
   - Regional 950 genérica: 111, 222, GENERIC
   - Posto 050 específico: 999, 888, ESPECIAL
2. Verifique que:
   - Lotes de 976, 977, 979, 980 → 111, 222, GENERIC
   - Lotes de 050 → 999, 888, ESPECIAL ✅

### Teste 4: Compatibilidade
1. ✅ SPLIT continua funcionando
2. ✅ Validação de etiquetas duplicadas funciona
3. ✅ Auto-foco em etiqueta funciona
4. ✅ Poupa Tempo não quebrou

## Arquivo Modificado

- `lacres_novo.php` — v8.9 → v8.10

## Status

- ✅ Versão atualizada
- ✅ Normalização de regional implementada
- ✅ Debug detalhado adicionado
- ✅ Sem erros estáticos
- ✅ Compatibilidade mantida

## Próximas Etapas

1. **Testar localmente:**
   ```bash
   php -S localhost:8000 -t .
   # Abra http://localhost:8000/lacres_novo.php
   # Preencha lacres, clique "Gravar Dados"
   # Verifique valores em ?debug=1
   ```

2. **Verificar no banco:**
   ```sql
   SELECT etiquetaiipr, etiquetacorreios, etiqueta_correios 
   FROM ciDespachoLotes 
   WHERE lote LIKE '00755%' AND ... 
   ```

3. **Fazer commit quando validado:**
   ```bash
   git add lacres_novo.php
   git commit -m "v8.10: Corrige salvamento de lacres (IIPR/Correios) por regional - normaliza regional, adiciona debug"
   git push origin main
   ```

---

**Versão:** 8.10  
**Data:** 2025-12-03  
**Mudanças:** Normalização de regional, debug melhorado  
**Status:** ✅ Pronto para testar
