<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
// Texto jurídico: manter a redação; aqui muda só a apresentação
$h2  = "k:flex k:gap-3 k:font-display k:text-xl k:font-semibold k:text-kade-950";
$num = "k:shrink-0 k:text-accent-ink";
$p   = "k:text-[15px] k:leading-relaxed k:text-ink-soft";
$sub = "k:flex k:gap-3 k:text-[15px] k:leading-relaxed k:text-ink-soft";
$subNum = "k:shrink-0 k:font-semibold k:text-ink";
$term = "k:font-semibold k:text-ink";
?>
<div class="k-ui k:bg-surface k:text-ink">

	<section class="k:bg-kade-900 k:text-white">
		<div class="k:mx-auto k:flex k:max-w-[880px] k:flex-col k:gap-3 k:px-4 k:py-12 k:md:px-6">
			<span class="k:text-[13px] k:font-semibold k:uppercase k:tracking-[1.5px] k:text-[#ffb766]">KADE Caminhões</span>
			<h1 class="k:font-display k:text-3xl k:leading-tight k:font-bold k:md:text-4xl">Termos de uso e condições gerais</h1>
		</div>
	</section>

	<div class="k:mx-auto k:max-w-[880px] k:px-4 k:py-10 k:md:px-6">
		<article class="k:flex k:flex-col k:gap-10 k:rounded-2xl k:border k:border-line k:bg-white k:p-6 k:md:p-10">

			<section class="k:flex k:flex-col k:gap-4">
				<h2 class="<?=$h2?>"><span class="<?=$num?>">1.</span>Definições do sistema KADE Caminhões</h2>
				<p class="<?=$p?>">O Sistema Kade Caminhões é um sistema de divulgação de veículos de cargas vazios, gratuito para os USUÁRIOS que anunciam a disponibilidade dos seus veículos, tanto para pessoas jurídicas quanto para físicas,
					através do website ou via mensagens de texto (SMS, WhatsApp ou Telegram).</p>
				<p class="<?=$p?>">A assinatura mensal é necessária apenas para as empresas CLIENTES, que necessitam visualizar os dados dos USUÁRIOS para a contratação dos seus serviços.</p>
				<dl class="k:m-0 k:flex k:flex-col k:gap-3 k:rounded-xl k:bg-surface k:p-5">
					<div class="<?=$sub?>">
						<dt class="<?=$subNum?>">1.1</dt>
						<dd class="k:m-0"><span class="<?=$term?>">USUÁRIOS:</span> Pessoa Física ou Jurídica que utiliza o sistema via WEB, SMS, WhatsApp ou Telegram para anunciar veículos, informando sua localização e se dispõe a receber ofertas de fretes.</dd>
					</div>
					<div class="<?=$sub?>">
						<dt class="<?=$subNum?>">1.2</dt>
						<dd class="k:m-0"><span class="<?=$term?>">CLIENTES:</span> Pessoa Física ou Jurídica que contratou os serviços da KADE CAMINHÕES e utiliza para contatar os USUÁRIOS anunciados no sistema KADE CAMINHÕES;</dd>
					</div>
				</dl>
			</section>

			<section class="k:flex k:flex-col k:gap-4">
				<h2 class="<?=$h2?>"><span class="<?=$num?>">2.</span>Objetivo do termo</h2>
				<div class="<?=$sub?>">
					<span class="<?=$subNum?>">2.1</span>
					<p>O objeto do presente termo é tornar ciente a prestação de serviços da KADE CAMINHÕES aos CLIENTES, serviço voltado para o anúncio de veículos vazios e que buscam por cargas em todo o território nacional,
						certo que tais informações disponibilizadas aos CLIENTES deverão ser tratadas de forma coerente e de acordo com a atividade da empresa.</p>
				</div>
			</section>

			<section class="k:flex k:flex-col k:gap-4">
				<h2 class="<?=$h2?>"><span class="<?=$num?>">3.</span>Condições gerais</h2>
				<div class="<?=$sub?>">
					<span class="<?=$subNum?>">3.1</span>
					<p>O CLIENTE concorda e reconhece que toda a informação proveniente deste sistema é considerada confidencial, e seu conteúdo não deverá em hipótese alguma ser repassado a pessoas terceiras,
						condizentes ou não com a atividade da empresa, O CLIENTE declara que as áreas restritas do website da KADE CAMINHÕES contêm informações de interesse particular da KADE CAMINHÕES e do CLIENTE,
						sendo expressamente proibido expor, serem acessadas ou transmitidas a terceiros, motivo pelo qual o CLIENTE é responsável pelo sigilo e correta utilização da senha e do sistema por seus representantes,
						devendo aplicar medidas de segurança e tomar as precauções necessárias para evitar a divulgação de tais informações a pessoas não autorizadas;</p>
				</div>
				<div class="<?=$sub?>">
					<span class="<?=$subNum?>">3.2</span>
					<p>O CLIENTE é o único responsável por todos os acessos realizados, o qual será realizado mediante utilização de login e senha cadastrados previamente,
						reconhecendo e declarando que o referido software constitui bem de propriedade intelectual da KADE CAMINHÕES e sua violação estará sujeita às penalidades previstas na legislação.</p>
				</div>
				<div class="<?=$sub?>">
					<span class="<?=$subNum?>">3.3</span>
					<p>A KADE CAMINHÕES não intermedia, e não se responsabiliza pelos contatos, acordos, contratações, pagamentos de fretes, saldos, ou quaisquer negociações entre CLIENTES e USUÁRIOS da ferramenta,
						ficando totalmente a cargo de ambas as partes todo e qualquer acordo afirmado.</p>
				</div>
				<div class="<?=$sub?>">
					<span class="<?=$subNum?>">3.4</span>
					<p>A KADE CAMINHÕES não se responsabiliza por problemas decorrentes de fatores externos que possam impedir ou interromper a prestação do serviço de envio de SMS ou de mensagens pelo WhatsApp e Telegram,
						relacionados a falhas nos envios devido regiões de sombra das operadoras de telefonia móvel celular, indisponibilidade dos aplicativos de mensagem ou da internet móvel, ou por indisponibilidade momentânea ou definitiva dos serviços de telefonia móvel celular em virtude de falhas técnicas,
						oscilações, suspensão do sinal de telefonia móvel ou problemas decorrentes de fatores atmosféricos ou geográficos, dentre outros.</p>
				</div>
			</section>

			<section class="k:flex k:flex-col k:gap-4">
				<h2 class="<?=$h2?>"><span class="<?=$num?>">4.</span>Assinatura mensal</h2>
				<p class="<?=$p?>">A assinatura mensal é necessária apenas para as empresas CLIENTES, que visualizam ou utilizam as informações dos USUÁRIOS do sistema KADE CAMINHÕES nas suas operações.</p>
				<div class="<?=$sub?>">
					<span class="<?=$subNum?>">4.1</span>
					<div class="k:flex k:flex-col k:gap-2">
						<p><span class="<?=$term?>">CADASTROS DE CLIENTES:</span> As novas empresas podem fazer o cadastro gratuitamente, conhecer e utilizar o sistema KADE CAMINHÕES por um período de 30 dias sem custos. Após este prazo,
							será informado ao CLIENTE a emissão do boleto bancário no valor da mensalidade com data de vencimento de 10 dias após a emissão,
							porém este boleto somente será enviado para os CLIENTES que estiverem de acordo, e passarão a utilizar o sistema nas suas operações.</p>
						<p>Se o pagamento do boleto não for efetuado no prazo de 15 dias do vencimento, a empresa será bloqueada para a visualização dos veículos vazios, ela manterá seu cadastro no sistema, mas não terá acesso aos serviços.</p>
					</div>
				</div>
				<div class="<?=$sub?>">
					<span class="<?=$subNum?>">4.2</span>
					<p><span class="<?=$term?>">COMO É FEITO O PAGAMENTO:</span> O pagamento da mensalidade é feito através do envio de boleto bancário enviado por e-mail, através do mercado pago, cartão de crédito, etc.</p>
				</div>
				<div class="<?=$sub?>">
					<span class="<?=$subNum?>">4.3</span>
					<p><span class="<?=$term?>">REATIVAÇÃO:</span> A reativação em caso de bloqueio, será efetuado através de solicitação por e-mail, sendo emitido um boleto no valor normal da assinatura,
						a liberação para visualização do sistema será efetuado imediatamente após a confirmação do pagamento.</p>
				</div>
			</section>

			<section class="k:flex k:flex-col k:gap-4">
				<h2 class="<?=$h2?>"><span class="<?=$num?>">5.</span>Cancelamento</h2>
				<p class="<?=$p?>">O Sistema Kade Caminhões reserva-se no direito de cancelar qualquer assinatura com a devolução proporcional do último pagamento efetuado.</p>
			</section>

		</article>
	</div>
</div>
