<footer class="site-footer">
    <img class="site-footer__cloud" src="{{ asset('media/cloud-2.webp') }}" alt="" loading="lazy" width="900" height="302">

    <div class="site-footer__inner">
        <div class="site-footer__sign">
            <img src="{{ asset('media/title.webp') }}" alt="Earth Orchestra" width="200" height="114" loading="lazy">
            <p>A visualizer that you can hear.</p>
        </div>

        <nav class="site-footer__col" aria-label="Footer">
            <h2>Wander</h2>
            <a href="{{ route('home') }}#programme">The six movements</a>
            <a href="{{ route('home') }}#orchestra">The full orchestra</a>
            <a href="{{ route('about') }}">About us</a>
            <a href="{{ route('credits') }}">Credits &amp; sources</a>
        </nav>

        <div class="site-footer__col">
            <h2>Data</h2>
            <p>Visualisations by NASA's Global Modeling and Assimilation Office, via the
                <a href="https://svs.gsfc.nasa.gov/" rel="noopener" target="_blank">Scientific Visualization Studio</a>.</p>
            <p class="site-footer__fine">For educational and artistic exploration - not for weather monitoring or scientific analysis.</p>
        </div>
    </div>

    <p class="site-footer__base">
        <span>{{ \App\Models\Page::creditText('footer_left') }}</span>
        <span>{{ \App\Models\Page::creditText('footer_right') }}</span>
    </p>
</footer>
