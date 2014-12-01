# Moduł Utils dla Laravel #

## 1. Przykłady użycia: ##


```
#!php
<?php

// Skracanie tresci artykulu do x znaków
App('Utils')->excerptString($article->content, 180);

// Formatowanie czasu - np. z "24242" do "06:44:02"
App('Utils')->seconds2hours(24242);

```