<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=<h, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SOP - Gerenciamento de ocorrências</title>
</head>

<body>
    <h3>Informações sobre sua ocorrência </h3>
    <ul>
        <li>Nome: {{ $data['body']->nome }} </li>
        <li>Titulo: {{ $data['body']->title }} </li>
        <li>Email: {{ $data['body']->email }} </li>
        <li>Endereço: {{ $data['body']->address }} </li>
        <li>Descrição: {{ $data['body']->description }} </li>
    </ul>
</body>

</html>
