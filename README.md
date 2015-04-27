Netinteractive\Report
=====================

## Changelog

### 1.0.8
- Fix bagu z namespace WriteCodeTrait

### 1.0.7
- dodanie WriteCodeTrait, który może być używany wyłącznie w komendach (Command)

### 1.0.6
- poprawka buga w arrayToModel

### 1.0.5
- poprawka buga w arrayToModel




## 1. Przykłady użycia: ##


```

    <?php
    
    // Skracanie tresci artykulu do x znaków
    App('Utils')->excerptString($article->content, 180);
    
    // Formatowanie czasu - np. z "24242" do "06:44:02"
    App('Utils')->seconds2hours(24242);
    
    ```