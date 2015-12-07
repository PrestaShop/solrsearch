{foreach from=$errors item=message}
    <div class="alert alert-danger">{$message|escape:'html'}</div>
{/foreach}

{foreach from=$successes item=message}
    <div class="alert alert-success">{$message|escape:'html'}</div>
{/foreach}

<div class="panel">
    <div class="panel-heading">
        {l s='Solr Search Configuration' mod='solrsearch'}
    </div>
    <section>
        <div class="row">
            <div class="col-lg-5 col-lg-offset-3">
                <h1>{l s='Configuration' mod='solrsearch'}</h1>
                <h2>{l s='1. Solr Server Location' mod='solrsearch'}</h2>
                {if $solrConnectionOK}
                    <div class="alert alert-success">
                        {l s='Congratulations, your solr connection seems to be up and running, you did it!' mod='solrsearch'}
                    </div>
                {else}
                    <div class="alert alert-info">
                        {l s='Please fill in your Solr server configuration info below. We could not connect to the solr server with the current settings.' mod='solrsearch'}
                    </div>
                {/if}
            </div>
        </div>
        <form class="form-horizontal" method="POST">
            <div class="form-group">
                <label class="control-label col-lg-3" for="solr_host">
                    {l s='Solr Host'}
                </label>
                <div class="col-lg-5">
                    <input class="form-control" id="solr_host" name="solrConfig[host]" value="{$solrConfig['host']}">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="solr_port">
                    {l s='Solr Port'}
                </label>
                <div class="col-lg-5">
                    <input class="form-control" id="solr_port" name="solrConfig[port]" type="number" value="{$solrConfig['port']}">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="solr_path">
                    {l s='Solr Path'}
                </label>
                <div class="col-lg-5">
                    <input class="form-control" id="solr_path" name="solrConfig[path]" value="{$solrConfig['path']}">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3" for="solr_core">
                    {l s='Solr Core'}
                </label>
                <div class="col-lg-5">
                    <input class="form-control" id="solr_core" name="solrConfig[core]" value="{$solrConfig['core']}">
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-5 col-lg-offset-3">
                    <button type="submit" class="btn btn-default pull-right">
                        <i class="process-icon-save"></i>{l s='Save' mod='solrsearch'}
                    </button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-lg-5 col-lg-offset-3">
                <h2>{l s='2. Solr Server Schema' mod='solrsearch'}</h2>
                {if $solrSchemaOK}
                    <div class="alert alert-success">
                        {l s='You are impressive. You managed to configure solr through xml. Enjoy now!' mod='solrsearch'}
                    </div>
                {else}
                    <div class="alert alert-info">
                        {l s='I know it sucks, but you really need to configure your solr schema...' mod='solrsearch'}
                    </div>
                {/if}
                <p>{l s='Sorry about this, but since solr is "enterprise software", you will need to copy-paste some XML as we cannot automate the configuration.' mod='solrsearch'}</p>

                <p>{l s='The following bit should go into your schema.xml file, in the fields section. Some fields may already be there so do not add them twice.'  mod='solrsearch'}</p>

                {l s='The file is usually under "/usr/share/solr/conf/schema.xml" but it is probably distribution-specific so check the manual for your system.' mod='solrsearch'}</p>
                <pre>{$fieldsForSchema|escape:'html'}</pre>
            </div>
        </div>
    </section>
</div>

<div class="panel">
    <div class="panel-heading">
        {l s='Solr Search Indexing' mod='solrsearch'}
    </div>
    <div class="row">
        <div class="col-lg-5 col-lg-offset-3">
            <h1>{l s='Product Indexing' mod='solrsearch'}</h1>
            {if $solrOK}
                <p>{l s='Click the button below and pray for your life.' mod='solrsearch'}</p>
                <form method="POST">
                    <input type="hidden" name="solrsearch[action]" value="reindex">
                    <button class="btn btn-primary" type="submit">{l s='Re-index all of the stuff'}</button>
                </form>
            {else}
                <div class="alert alert-warning">
                    {l s='Bad luck, something\'s not right. Please complete the configuration of solr.' mod='solrsearch'}
                </div>
            {/if}
        </div>
    </div>
</div>
