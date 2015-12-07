<div class="panel">
    <div class="panel-heading">
        {l s='Solr Search' mod='solrsearch'}
    </div>
    <div class="panel-body">
        <section>
            <h1>{l s='Configuration' mod='solrsearch'}</h1>
            <p>{l s='Sorry about this, but since solr is "enterprise software", you will need to copy-paste some XML as we cannot automate the configuration.' mod='solrsearch'}</p>

            <p>{l s='The following bit should go into your solr.xml file, in the fields section. Some fields may already be there so do not add them twice.'  mod='solrsearch'}</p>

            {l s='The file is usually under "/usr/share/solr/conf/schema.xml" but it is probably distro-specific so check the manual for your system:' mod='solrsearch'}</p>
            <pre>{$fieldsForSchema|escape:'html'}</pre>
        </section>
        <form method="POST">
            <input type="hidden" name="solrsearch[action]" value="reindex">
            <button class="btn btn-primary" type="submit">{l s='Re-index all of the stuff'}</button>
        </form>
    </div>
</div>
