empleados = [18, 29, 28, 17, 39, ["hola", ["me llamo", "angel", "abril"]]]

frase = ""

for elemento in empleados:
    if isinstance(elemento, list):          # Si es una lista
        for sub in elemento:                # Recorremos sus elementos
            if isinstance(sub, list):       # Si hay otra lista dentro
                for palabra in sub:         # Recorremos esa lista
                    frase += palabra + " "  # Concatenamos con espacio
            else:
                frase += sub + " "          # Concatenamos directamente

print(frase.strip())
