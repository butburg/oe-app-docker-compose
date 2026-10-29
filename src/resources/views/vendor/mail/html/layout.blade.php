<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
{{-- the mail is dark by design, clients should not invert it --}}
<meta name="color-scheme" content="dark">
<meta name="supported-color-schemes" content="dark">
<style>
@media only screen and (max-width: 600px) {
.inner-body, .footer { width: 100% !important; }
}
</style>
</head>
<body bgcolor="#071D20">
{{-- background also as attribute on the wrapper table: some clients drop the body styles --}}
<table class="wrapper" width="100%" bgcolor="#071D20" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
{{ $header ?? '' }}

<tr>
<td align="center">
<table class="inner-body" align="center" width="570" bgcolor="#0D292D" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell">
{{ Illuminate\Mail\Markdown::parse($slot) }}

{{ $subcopy ?? '' }}
</td>
</tr>
</table>
</td>
</tr>

{{ $footer ?? '' }}
</table>
</td>
</tr>
</table>
</body>
</html>
