<?php
/** @package VoucherManager */
declare(strict_types=1);
$root = dirname( __DIR__, 2 );
spl_autoload_register( static function ( string $class ) use ( $root ): void { $prefix='VoucherManager\\'; if ( str_starts_with( $class, $prefix ) ) { $file=$root.'/src/'.str_replace('\\','/',substr($class,strlen($prefix))).'.php'; if ( is_readable($file) ) { require_once $file; } } } );
$assert = static function ( bool $condition, string $message ): void { if ( ! $condition ) { throw new RuntimeException( 'Pool lifecycle assertion failed: ' . $message ); } };
final class LifecycleMemoryRepository implements VoucherManager\Domain\Pool\PoolLifecycleRepository {
	public array $codes = array( array('status'=>'available','code'=>'SECRET-A'), array('status'=>'assigned','code'=>'SECRET-B') ); public array $imports = array(1,2); public bool $pool=true; public bool $fail=false;
	public function deletion_summary( int $pool_id ): ?array { unset($pool_id); return $this->pool ? array('total'=>count($this->codes),'available'=>count(array_filter($this->codes,fn($c)=>'available'===$c['status'])),'assigned'=>count(array_filter($this->codes,fn($c)=>'assigned'===$c['status'])),'imports'=>count($this->imports)) : null; }
	public function delete_available_codes( int $pool_id ): int { unset($pool_id); $before=count($this->codes); $this->codes=array_values(array_filter($this->codes,fn($c)=>'available'!==$c['status'])); return $before-count($this->codes); }
	public function delete_pool_with_data( int $pool_id ): array { unset($pool_id); $snapshot=array($this->codes,$this->imports,$this->pool); try { $code_count=count($this->codes); $this->codes=[]; if($this->fail){ throw new RuntimeException('Injected failure containing SECRET-A'); } $import_count=count($this->imports); $this->imports=[]; $this->pool=false; return array('deleted_code_count'=>$code_count,'deleted_import_count'=>$import_count); } catch(Throwable $e){ [$this->codes,$this->imports,$this->pool]=$snapshot; throw $e; } }
}
final class LifecycleLogRepository implements VoucherManager\Domain\Log\LogRepository { public array $entries=[]; public function add(string $event_type,string $message,array $context=[]):void{$this->entries[]=compact('event_type','message','context');} }
$repo=new LifecycleMemoryRepository(); $logs=new LifecycleLogRepository(); $service=new VoucherManager\Domain\Pool\PoolLifecycleService($repo,new VoucherManager\Domain\Log\OperationalLogger($logs));
$assert(1===$service->delete_available_codes(7),'Delete-available must report affected available rows.');
$assert(1===count($repo->codes) && 'assigned'===$repo->codes[0]['status'],'Assigned codes must survive delete-available.');
$assert('pool.available_codes_deleted'===$logs->entries[0]['event_type'],'Delete-available event vocabulary changed.');
$assert(!str_contains(json_encode($logs->entries,JSON_THROW_ON_ERROR),'SECRET-'),'Lifecycle logs must not contain voucher values.');
$repo=new LifecycleMemoryRepository(); $service=new VoucherManager\Domain\Pool\PoolLifecycleService($repo,new VoucherManager\Domain\Log\OperationalLogger($logs)); $deleted=$service->delete_pool(7);
$assert(!$repo->pool && []===$repo->codes && []===$repo->imports,'Full deletion must remove pool, codes and imports.'); $assert(2===$deleted['deleted_code_count'] && 2===$deleted['deleted_import_count'],'Full deletion counts must be retained for logging.');
$repo=new LifecycleMemoryRepository(); $repo->fail=true; $service=new VoucherManager\Domain\Pool\PoolLifecycleService($repo,new VoucherManager\Domain\Log\OperationalLogger($logs)); try{$service->delete_pool(7);}catch(RuntimeException){}
$assert($repo->pool && 2===count($repo->codes) && 2===count($repo->imports),'A failed full deletion must roll back all destructive changes.');
$events=array_column($logs->entries,'event_type'); $assert(in_array('pool.deleted',$events,true) && in_array('pool.delete_failed',$events,true),'Lifecycle event vocabulary must include success and failure.');
$admin=file_get_contents($root.'/src/Admin/PoolAdmin.php'); $template=file_get_contents($root.'/templates/admin/pool-danger-zone.php'); $confirmation=file_get_contents($root.'/templates/admin/pool-delete-available-confirmation.php'); $pools=file_get_contents($root.'/templates/admin/pools.php');
$assert(str_contains($admin,"current_user_can( 'manage_options' )") && str_contains($admin,'check_admin_referer'),'Destructive admin actions must retain capability and nonce protection.');
$assert(str_contains($admin,"isset( \$_POST['pool_id'] )") && str_contains($confirmation,'method="post"'),'Destructive execution must use POST.');
$assert(str_contains($template,"'confirm-delete-available'") && !str_contains($template,'name="action" value="voucher_manager_delete_available_codes"'),'Danger Zone must route available-code deletion through a dedicated confirmation view.');
$assert(str_contains($confirmation,'confirm_delete_available') && str_contains($confirmation,'required'),'Available-code deletion must require explicit acknowledgement.');
$assert(str_contains($admin,"! \$confirmed") && str_contains($admin,"'confirmation_required'"),'The admin boundary must reject an unconfirmed available-code deletion POST.');
$assert(str_contains($template,'pool_name_confirmation'),'Full deletion must require exact pool-name confirmation.');
$assert(str_contains($pools,'Danger Zone') && !str_contains($pools,'delete_blocked'),'Pool overview must expose the Danger Zone instead of hiding deletion.');
fwrite(STDOUT,"Pool lifecycle integrity OK: scoped deletion, confirmation boundary, atomic rollback and privacy-safe events verified.".PHP_EOL);
