---
layout: home

hero:
  name: "LatteView for CakePHP"
  tagline: "Bringing the power of Latte templates to your CakePHP application"
  image:
    src: lattelogo.png
    alt: Latte
  actions:
    - theme: brand
      text: Documentation
      link: /getting-started

features:
  - icon: 🎨 
    title: Latte Templates
    details: Leverage the elegant and powerful Latte templating engine with .latte files in your CakePHP applications
  - icon: 🍰
    title: CakePHP
    details: Custom CakePHP-specific tags, filters, and functions for optimal framework compatibility and developer experience    
  - icon: 🔒
    title: Secure Sandbox
    details: Built-in sandbox mode ensures safe template execution and prevents unauthorized code execution
  - icon: 🐛
    title: DebugKit Integration
    details: Comprehensive debugging support with dedicated DebugKit panel for template inspection and performance monitoring
---

📄 layout.latte

```latte
<!DOCTYPE html>
<html>
<head>
    {Html charset}
    <title>{block title}Homepage{/block} - My app</title>
    {* $this->Html->css('site') *}
    {Html css 'site'}
    {Html js 'app'}

    {* $this->fetch('meta') *}
    {fetch meta}
    {fetch css}
    {fetch script}
</head>
<body>
    <header>
      <h1>{block title}Latte + CakePHP = Awesome{/block}</h1>
    </header>
    <main>
      {include content}
    </main>
    <footer>
      {block footer}
        Copyright {$today|format:'Y'}
      {/block}
    </footer>
</body>
</html>
```

📄 add.latte

```latte
{block title}Create article{/block}

{block content}
{* $this->Form->create($user) *}
<form n:context="$user">
  <control n:name="first_name" />
  <control n:name="last_name" />
  <control n:name="email" /> 
  <control n:name="password" />
  <control n:name="bio" rows="10" />
  <button type="submit">{_'Save article'}</button>
  <a n:named="users:index">{_'Go back'}</a>
</form>
```