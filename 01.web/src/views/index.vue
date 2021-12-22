<style scoped lang="less">
.my_header {
  background: #0f8de9;
  color: #fff;
  text-align: center;
  height: 64px;
}
.my_content {
  background-color: #fff;
}
.my_row {
  margin-top: 50px;
}
.my_footer {
  text-align: center;
  height: 69;
}
</style>
<template>
  <div>
    <Layout>
      <Header class="my_header"
        ><h2>IP路由追踪-PHP源码实现</h2></Header
      >
      <Content class="my_content" :style="{ minHeight: Height + 'px' }">
        <Row type="flex" justify="center" align="middle" class="my_row">
          <Col span="16">
            <Tabs type="card">
              <TabPane label="ICMP">
                <br />
                <Input
                  style="width: 600px; margin: 0 auto"
                  size="large"
                  search
                  enter-button="追踪"
                  placeholder="输入域名或IP地址..."
                  v-model="icmp_input_ip"
                  @on-search="GetICMP"
                />
                <br />
                <br />
                <Steps :current="current" v-if="icmp_table_show">
                  <Step title="开始追踪" :content="icmp_input_ip"></Step>
                  <Step title="追踪中"></Step>
                  <Step title="未到达目标IP"></Step>
                  <Step title="已到达目标IP"></Step>
                </Steps>
                <br />
                <br />
                <Table
                  :loading="icmp_table_loading"
                  :columns="icmp_column"
                  :data="icmp_data"
                  v-if="icmp_table_show"
                >
                  <template slot-scope="{ index }" slot="icmp_index">
                    <strong>{{ index + 1 }}</strong>
                  </template>
                </Table>
                <br />
              </TabPane>
              <TabPane label="UDP-未实现">
                <br />
                <Input
                  style="width: 600px; margin: 0 auto"
                  size="large"
                  search
                  enter-button="追踪"
                  placeholder="输入域名或IP地址..."
                />
                <br />
                <br />
              </TabPane>
              <TabPane label="TCP-未实现">
                <br />
                <Input
                  style="width: 600px; margin: 0 auto"
                  size="large"
                  search
                  enter-button="追踪"
                  placeholder="输入域名或IP地址..."
                />
                <br />
                <br />
              </TabPane>
            </Tabs>
          </Col>
        </Row>
      </Content>
      <Footer class="my_footer">©2021 witersen. All rights reserved.</Footer>
    </Layout>
  </div>
</template>
<script>
export default {
  data() {
    return {
      current: 0,
      icmp_table_loading: false,
      icmp_table_show: false,
      icmp_input_ip: "",
      icmp_column: [
        {
          title: "序号",
          key: "icmp_index",
          slot: "icmp_index",
        },
        {
          title: "IP",
          key: "icmp_ip",
        },
        {
          title: "TTL",
          key: "icmp_hop",
        },
        {
          title: "rdns",
          key: "icmp_rdns",
        },
        {
          title: "IP地理位置",
          key: "icmp_geograph",
        },
      ],
      icmp_data: [],
      Height: 0,
    };
  },
  mounted() {
    this.Height = document.documentElement.clientHeight - 133;
    window.onresize = function () {
      this.Height = document.documentElement.clientHeight - 133;
    };
  },
  methods: {
    GetICMP() {
      var that = this;
      that.icmp_data = [];
      that.icmp_table_show = true;
      that.icmp_table_loading = true;
      that.current = 0;
      var data = {
        ip: that.icmp_input_ip,
        geograph: 1,
        rdns: 1,
        seq: 1,
        WaitTime: 0,
        MaxHops: 30,
        sleeptime: 0,
        packcount: 10,
      };
      that.$axios
        .post("/api.php?c=icmp&a=Traceroute", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.icmp_data = result.data.hops;
            that.icmp_table_loading = false;
            if(result.data.arrive==false){
              that.current = 2;
            }else{
              that.current = 3;
            }
          } else {
            that.$Message.error(result.message);
            that.icmp_table_show = false;
            that.icmp_table_loading = false;
            that.current = 2;
          }
        })
        .catch(function (error) {
          console.log(error);
          that.icmp_table_loading = false;
        });
        that.current = 1;
    },
  },
};
</script>
