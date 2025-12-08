<!-- <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<style>
    body {
        font-family: "Cairo", sans-serif;
        direction: rtl;
        text-align: right;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table, th, td {
        border: 1px solid #000;
    }

    th, td {
        padding: 8px;
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">

</head>
<body>

<h1 style="text-align:center">تقرير الشكاوى</h1>

<p>تم التوليد في: {{ $generated_at }}</p>
<p>بواسطة: {{ $generated_by }}</p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>النوع</th>
            <th>العنوان</th>
            <th>الحالة</th>
            <th>الجهة</th>
            <th>المواطن</th>
        </tr>
    </thead>
    <tbody>
        @foreach($complaints as $c)
        <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->type }}</td>
            <td>{{ $c->address }}</td>
            <td>
                @if($c->status == 1) جديدة
                @elseif($c->status == 2) قيد المعالجة
                @elseif($c->status == 3) منجزة
                @else مرفوضة
                @endif
            </td>
            <td>{{ $c->agency->name ?? '-' }}</td>
            <td>{{ $c->user->phone ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html> -->
