# Publicación Emergente
  
Este plugin permite mostrar hasta 5 entradas seleccionadas como "Mostrar en la ventana emergente" en una ventana emergente (popup) en la página de inicio de tu sitio WordPress. El contenido del popup puede incluir el título, extracto y un botón de "Leer más".

---

## 🎯 Características

- Meta box en el editor de entradas: "Mostrar en la ventana emergente" (casilla de verificación en la barra lateral).
- Guarda hasta 5 entradas marcadas; si se marcan más, las entradas más antiguas se desmarcan automáticamente para mantener un máximo de 5 publicaciones activas.
- Popup aparece automáticamente 2 segundos después de cargar la página de inicio (solo en la página principal).
- Personalizable desde el panel de administración:
  - Título del popup
  - Colores (primario, fondo, texto, título)
  - Borde redondeado (px)
  - Frecuencia de aparición (cada X minutos)
- Bloquea scroll e interacción con el fondo mientras está visible (se crea un overlay semi-transparente).
- Compatible con dispositivos móviles (diseño responsivo y ancho máximo configurado).
- El popup se muestra solo una vez cada X minutos usando `localStorage` (clave: `popupLastShown`).
- Enlaces de "Leer más" abren en una nueva pestaña con `rel="noopener"` por seguridad.
- Diferentes layouts según la cantidad de posts: diseño enfocado para una sola entrada o lista para múltiples entradas (hasta 5).

---

## ⚙️ Instalación

1. Copia la carpeta del plugin en:  
   `wp-content/plugins/publicacion-emergente/`
2. Activa el plugin desde el panel de **Plugins** en WordPress.
3. Dirígete a **Publicación Emergente** en el menú de administración para configurar los ajustes del popup.
4. Edita una entrada y marca **"Mostrar en la ventana emergente"** en la barra lateral para incluirla en el popup.

---

## 🧪 Uso

- Ve a la sección **Entradas**.
- Edita la entrada que deseas mostrar en el popup.
- Marca la casilla **"Mostrar en la ventana emergente"** y actualiza.
- El plugin mostrará las entradas seleccionadas (las 5 más recientes marcadas) automáticamente en la página de inicio.
- Si desmarcas la casilla y actualizas la entrada, esa entrada se elimina de la lista de publicaciones del popup.

---

## 🖌 Personalización

Desde **Publicación Emergente** (menú de administración) puedes modificar:

- Título del encabezado
- Color del encabezado y botón (color primario)
- Color del texto y fondo del popup
- Borde redondeado del popup (en píxeles)
- Intervalo de reaparición (en minutos) — controla cuánto tiempo debe pasar antes de volver a mostrar el popup a un mismo visitante (se almacena en `localStorage`).

---

## 🛠 Requisitos

- WordPress 5.0 o superior
- Tema con soporte para `wp_footer()` (para inyectar el HTML/JS del popup)

---

## 📌 Notas técnicas

- Solo las entradas (`post`) son consideradas para el popup; el plugin utiliza la meta key `_show_in_popup` con valor `'1'` para marcar una entrada.
- Al marcar una entrada como "Mostrar en la ventana emergente" se guarda la meta y, si ya hay más de 4 entradas previamente marcadas, las más antiguas se desmarcan automáticamente para mantener un máximo de 5 entradas activas.
- El popup sólo se inyecta en la página principal (front page) y se muestra después de 2 segundos si la última aparición fue hace más tiempo que el intervalo configurado.
- El popup crea un overlay que bloquea la interacción con el fondo y puede cerrarse con el botón de cierre o haciendo clic fuera del popup (overlay).
- Los enlaces de cada entrada abren en una nueva pestaña (target="_blank") y usan `rel="noopener"`.
- Local storage: la clave usada para controlar la frecuencia es `popupLastShown` y almacena un timestamp en milisegundos.

---

## Cambios relevantes (actuales)

- El plugin muestra hasta 5 entradas seleccionadas (comportamiento por defecto). Anteriormente la documentación podía indicar una sola entrada; el comportamiento actual permite múltiples entradas y prioriza las más recientemente marcadas.

---

## Contribuciones y soporte

Si encuentras un error o tienes una sugerencia, abre un issue o contribuye con un pull request.
