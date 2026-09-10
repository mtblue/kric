import re, shutil

renewal = r'c:\Users\web0012osk\Documents\kric\www\kric.co.jp\www\renewal\css\member.css'
theme   = r'c:\Users\web0012osk\Documents\kric\www\kric.co.jp\www\wp-content\themes\kric-renewal\css\member.css'

shutil.copy2(renewal, theme)

with open(theme, 'rb') as f:
    raw = f.read().decode('utf-8')

def format_css(css):
    out = []; depth = 0; i = 0; n = len(css)
    while i < n:
        c = css[i]
        if c == '{':
            while out and out[-1] == ' ': out.pop()
            out.append(' {\n'); depth += 1; i += 1
            while i < n and css[i] in ' \t\r\n': i += 1
            if i < n and css[i] != '}': out.append('  ' * depth)
            continue
        elif c == '}':
            while out and out[-1] in ' \t': out.pop()
            if out and out[-1] != '\n': out.append('\n')
            depth -= 1; out.append('  ' * depth + '}\n')
            if depth == 0: out.append('\n')
            i += 1
            while i < n and css[i] in ' \t\r\n': i += 1
            if i < n and css[i] != '}': out.append('  ' * depth)
            continue
        elif c == ';':
            out.append(';\n'); i += 1
            while i < n and css[i] in ' \t\r\n': i += 1
            if i < n and css[i] != '}': out.append('  ' * depth)
            continue
        elif c in ' \t' and out and out[-1] in ' \t\n':
            i += 1; continue
        else:
            out.append(c)
        i += 1
    return ''.join(out).strip() + '\n'

formatted = format_css(raw)
formatted = re.sub(r'\n{3,}', '\n\n', formatted)
formatted = re.sub(r'\*/([^\n])', r'*/\n\1', formatted)

# U+7E00-7FFF: Shift-JIS garble / U+0080-009F: C1 control / U+F800-FFFC: PUA / U+FF65-FF9F: half-width kana
GARBLE = re.compile(u"[" + chr(0x7e00) + "-" + chr(0x7fff) + chr(0x0080) + "-" + chr(0x009f) + chr(0xf800) + "-" + chr(0xfffc) + chr(0xff65) + "-" + chr(0xff9f) + "]")

def remove_garbled(m):
    return '' if GARBLE.search(m.group()) else m.group()

result = re.sub(r'/\*.*?\*/', remove_garbled, formatted, flags=re.DOTALL)
result = re.sub(r'\n{3,}', '\n\n', result)

with open(theme, 'w', encoding='utf-8', newline='\n') as f:
    f.write(result)

remaining = re.findall(r'/\*.*?\*/', result, re.DOTALL)
print('Remaining comments: %d' % len(remaining))
for c in remaining:
    print(' ', repr(c[:70]))