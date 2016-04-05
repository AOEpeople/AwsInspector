<?php

namespace AwsInspector\Command\Graph;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NetworkCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('graph:network')
            ->setDescription('Show network graph');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tags = ['Project' => 'search'];


        //$data = [];
        //
        //$securityGroupRepository = new \AwsInspector\Model\SecurityGroup\Repository();
        //$securityGroups = $securityGroupRepository->findSecurityGroupsByTags($tags);
        //foreach ($securityGroups as $securityGroup) { /* @var $securityGroup \AwsInspector\Model\SecurityGroup\SecurityGroup */
        //    $data[$securityGroup->getGroupId()]['sg'] = $securityGroup;
        //    foreach ($securityGroup->getIngressSecurityGroupIds() as $ingressSecurityGroupId) {
        //        if (!isset($data[$ingressSecurityGroupId])) {
        //            $data[$ingressSecurityGroupId] = [];
        //        }
        //    }
        //}
        //
        //$elbRepository = new \AwsInspector\Model\Elb\Repository();
        //$elbs = $elbRepository->findElbsByTags($tags);
        //foreach ($elbs as $elb) { /* @var $elb \AwsInspector\Model\Elb\Elb */
        //    $i=0;
        //    foreach ($elb->getSecurityGroups() as $securityGroupId) {
        //        if (!isset($data[$securityGroupId])) {
        //            $data[$securityGroupId] = [];
        //        }
        //        $data[$securityGroupId]['resources'][] = $elb->getTag('Name') . ((++$i > 1) ? $i : '');
        //    }
        //}
        //
        //$instancesRepository = new \AwsInspector\Model\Ec2\Repository();
        //$instances = $instancesRepository->findEc2InstancesByTags($tags);
        //foreach ($instances as $instance) { /* @var $instance \AwsInspector\Model\Ec2\Instance */
        //    $i = 0;
        //    foreach ($instance->getSecurityGroups() as $tmp) {
        //        $securityGroupId = $tmp['GroupId'];
        //        if (!isset($data[$securityGroupId])) {
        //            $data[$securityGroupId] = [];
        //        }
        //        $data[$securityGroupId]['resources'][] = $instance->getTag('Name') . ((++$i > 1) ? $i : '');
        //    }
        //}
        //
        //$rdsRepository = new \AwsInspector\Model\Rds\Repository();
        //$databases = $rdsRepository->findDatabasesByTags($tags);
        //foreach ($databases as $database) { /* @var $database \AwsInspector\Model\Rds\Database */
        //    $i = 0;
        //    foreach ($database->getVpcSecurityGroups() as $tmp) {
        //        $securityGroupId = $tmp['VpcSecurityGroupId'];
        //        if (!isset($data[$securityGroupId])) {
        //            $data[$securityGroupId] = [];
        //        }
        //        $data[$securityGroupId]['resources'][] = $database->getTag('Name') . ((++$i > 1) ? $i : '');
        //    }
        //}
        //
        //$elastiCacheRespository = new \AwsInspector\Model\ElastiCache\Repository();
        //$caches = $elastiCacheRespository->findCacheClustersByTags($tags);
        //foreach ($caches as $cache) { /* @var $cache \AwsInspector\Model\ElastiCache\CacheCluster */
        //    $i = 0;
        //    foreach ($cache->getSecurityGroups() as $tmp) {
        //        $securityGroupId = $tmp['SecurityGroupId'];
        //        if (!isset($data[$securityGroupId])) {
        //            $data[$securityGroupId] = [];
        //        }
        //        $data[$securityGroupId]['resources'][] = $cache->getTag('Name') . ((++$i > 1) ? $i : '');
        //    }
        //}
        //
        //// load information about the missing security groups
        //foreach ($data as $securityGroupId => &$d) {
        //    if (empty($d['sg'])) {
        //        $d['sg'] = $securityGroupRepository->findSecurityGroupByGroupId($securityGroupId);
        //    }
        //}
        //
        //file_put_contents('/tmp/data', serialize($data));

        $data = unserialize(file_get_contents('/tmp/data'));




        $dot = array();
        $dot[] = 'digraph G {';
        $dot[] = '    splines=false;';
        $dot[] = '    layout=fdp;';
        $dot[] = '    overlap=false;';
        $dot[] = '    graph [style="dashed,rounded", color=red, fontsize=10, fontcolor=red];';
        $dot[] = '    node [fontname="helvetica", shape=plaintext, style="filled", fillcolor="#dddddd"];';
        $dot[] = '    edge [fontsize=10, fontname="helvetica", arrowhead=vee, arrowtail=inv, arrowsize=.7, color="red", style=dashed];';
        $dot[] = '    fontname="helvetica";';

        foreach ($data as $securityGroupId => $d) {

            $dot[] = "    subgraph \"cluster_$securityGroupId\" {";
            $dot[] = "        label=\"{$d['sg']->getGroupName()}\";";
            if (isset($d['resources']) && count($d['resources'])) {
                foreach ($d['resources'] as $resource) {
                    /* @var $resource string */
                    $dot[] = "        \"{$resource}\" [margin=.5, image=\"elb.png\", labelloc=b];";
                }
            }
            $dot[] = "    }";

            foreach ($d['sg']->getIngressSecurityGroupIds() as $label => $sourceSecurityGroupId) {
                $dot[] = "    \"cluster_$sourceSecurityGroupId\" -> \"cluster_$securityGroupId\" [label=\"$label\"];";
            }

        }


        $dot[] = '}';
        $output->writeln(implode("\n", $dot));
        return;



        $dot[] = '';
        $dot[] = '    splines=false;';
        $dot[] = '    rankdir=LR;';
        $dot[] = '    layout=fdp;';
        $dot[] = '    edge [fontsize=6, fontname="verdana", arrowhead=vee, arrowtail=inv, arrowsize=.7, color="#dddddd"];';
        $dot[] = '    node [fontname="verdana", shape=plaintext, style="filled", fillcolor="#dddddd"];';
        $dot[] = '    fontname="Verdana";';
        $dot[] = '';


        //foreach ($collector->getClassesByModule() as $module => $classes) {
        //    $dot[] = "    subgraph cluster_$module {";
        //    $dot[] = "        style=filled;";
        //    $dot[] = "        color=\"#eeeeee\";";
        //    $output[] = "        label=\"$module\";";
        //    foreach ($classes as $class) {
        //        $dot[] = "        node [label=\"$class\"] $class;";
        //    }
        //    $dot[] = "    }";
        //    $dot[] = '';
        //}


        foreach ($securityGroups as $securityGroup) { /* @var $securityGroup \AwsInspector\Model\SecurityGroup\SecurityGroup */

            $links = [];
            foreach ($securityGroup->getIpPermissions() as $type) {

                $protocol = ($type['IpProtocol'] == '-1') ? 'All' : $type['IpProtocol'];
                $ports = 'All';
                if (!empty($type['FromPort']) && !empty($type['ToPort'])) {
                    if ($type['FromPort'] == $type['ToPort']) {
                        $ports = $type['FromPort'];
                    } else {
                        $ports = $type['FromPort'] . '-' .$type['toPort'];
                    }
                }

                // node with label
                $sgLabels[$securityGroup->getGroupId()] = $securityGroup->getGroupName();

                // relations to other security groups
                foreach ($type['UserIdGroupPairs'] as $userIdGroupPair) {
                    $sourceSecurityGroup = $userIdGroupPair['GroupId'];
                    if (!isset($sgLabels[$sourceSecurityGroup])) {
                        $sgLabels[$sourceSecurityGroup] = false; // look up label later
                    }
                    $links[$sourceSecurityGroup][$protocol][] = $ports;
                }

                // relations to IP ranges
                foreach ($type['IpRanges'] as $ipRange) {
                    $links[$ipRange['CidrIp']][$protocol][] = $ports;
                }
            }

            //var_dump($links);
            foreach ($links as $source => $data) {
                $tmp = [];
                foreach ($data as $protocol => $linksPerProtocol) {
                    //var_dump($linksPerProtocol);
                    sort($linksPerProtocol);
                    $tmp[$protocol] = implode(',', $linksPerProtocol);
                }
                $tmp2 = [];
                foreach ($tmp as $protocol => $links) {
                    $tmp2[] = "$protocol:$links";
                }
                $label = implode(', ', $tmp2);
                $dot[] = "    \"$source\" -> \"{$securityGroup->getGroupId()}\" [label=\"$label\"];";
            }
        }

        foreach ($sgLabels as $securityGroupId => $label) {
            if (!$label) {
                $label = $repository->findSecurityGroupByGroupId($securityGroupId)->getGroupName();
            }
            $dot[] = "    \"{$securityGroupId}\"[label=\"{$label}\"];";
        }

        $dot[] = '}';

        $output->writeln(implode("\n", $dot));
    }

}


//digraph G {
//    a -> b [ label="a to b" ];
// b -> c [ label="another label"];
//}