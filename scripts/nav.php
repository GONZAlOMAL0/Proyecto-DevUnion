<nav>

  <div class="underline"></div>
  <div class="underline"></div>
  <div class="underline"></div>
  <a onclick="ul(0)">Home</a>
  <a onclick="ul(1)">Videos</a>
  <a onclick="ul(2)">Playlists</a>
  <a href="login.php" onclick="ul(3)">Login</a>
  <!-- <a onclick="ul(4)">Channels</a> -->
</nav>
<style>
    :root {
    --underline-height: 0.5em;
    --transition-duration: 0.5s;
    }

    body {
    font-family: system-ui, sans-serif;
    background: whitesmoke;
    min-height: 100vh;

    }

    nav {
    position: relative;
    white-space: nowrap;
    background: white;
    padding: var(--underline-height) 0;
    /* margin: 2em 0; */
    box-shadow: 0 1em 2em rgba(0, 0, 0, 0.05);
    }

    .underline {
    display: block;
    position: absolute;
    z-index: 0;
    bottom: 0;
    left: 0;
    height: var(--underline-height);
    width: 20%;
    background: black;
    pointer-events: none;
    mix-blend-mode: multiply;
    transition: transform var(--transition-duration) ease-in-out;
    }

    .underline:nth-child(1) {
    background: yellow;
    transition: calc(var(--transition-duration) * 0.8);
    }

    .underline:nth-child(2) {
    background: cyan;
    transition: calc(var(--transition-duration) * 1.2);
    }

    .underline:nth-child(3) {
    background: magenta;
    }

    a {
    display: inline-block;
    z-index: 10;
    width: 20%;
    padding: 1em 0;
    text-align: center;
    cursor: pointer;
    color: #000;
    text-decoration: none;
    }
</style>

<script>
    function ul(index) {
        console.log('click!' + index)
        
        var underlines = document.querySelectorAll(".underline");

        for (var i = 0; i < underlines.length; i++) {
            underlines[i].style.transform = 'translate3d(' + index * 100 + '%,0,0)';
        }
    }

</script>