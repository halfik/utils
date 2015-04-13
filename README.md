Netinteractive\Report
=====================

## Changelog

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