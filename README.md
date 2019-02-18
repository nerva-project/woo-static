# Woo-Static

Woo-Static is a WooCommerce plugin for stores that wish to accept XNV as an additional payment option. This is one of a pair of WooCommerce plugins, the other being Woo-Dynamic. The main differentiator is that Woo-Dynamic is designed to convert fiat values into an equivalent XNV value based on the current market price. Woo-Static is designed to have a fixed conversion rate which is specified in the plugin settings.

## 1: Activating the plugin

* Put the plugin in the correct directory: You will need to put the folder named `nerva` from this repo/unzipped release into the wordpress plugins directory. This can be found at `path/to/wordpress/folder/wp-content/plugins`

* Activate the plugin from the WordPress admin panel: Once you login to the admin panel in WordPress, click on "Installed Plugins" under "Plugins". Then simply click "Activate" where it says "Nerva - WooCommerce Gateway"

At this point you will be able to see the gateway setting page and there will be a message at the top saying `[ERROR] Failed to connect to nerva-wallet-rpc at localhost port`. This means the payment gateway cannot contact the nerva rpc wallet and we will configure this in the next step.

## 2: Configuring your wallet backend

In order for the plugin to detect when payments have been made, you must run an instance of nerva-wallet-rpc on your server, connected to the wallet your store will be receiving payments to. There are a couple of different options in setting this up with varying levels of security. we will discuss these and the potential ramifications of each decision.

### 2a: Setting up the wallet

The recommended method to setting up a wallet is to create a view-only wallet. This type of wallet allows you to see payments that are coming into the wallet, but you cannot spend funds from the wallet. In the situation of a store accepting Nerva as a payment, this is the preferred and most secure method.

To create a view-only wallet you first create a regular wallet with nerva-wallet-cli. The process is not discussed here as it is fairly rudimentary to anyone who has used nerva before. Once that wallet is created, we need to obtain the address and private view key to create the view-only wallet. once we have these we run  
`nerva-wallet-cli --generate-from-view-key <wallet-name>`  
where `<wallet-name>` is the name you want to give to your wallet. You will be prompted to enter the address and private view key for this new wallet, along with a password. Once done, the wallet will act in the same way as a regular wallet for receiving payments, but you will not be able to transfer funds out of the wallet.

NOTE: View-only wallet will only show the total amount of payments received into the wallet. They do not count of consider outgoing transactions. Therefore, the balance reflected by a view-only wallet will only be correct if it is used to only receive payments. This may not be desirable or possible in all situations.

NOTE: Be sure to backup the wallet you have created. A good backup includes all information. Address, mnemonic seed, and wallet keys. You can get all this information from the wallet with the commands  
`address`  
`seed`  
`viewkey`  
`spendkey`  
And entering the wallet password when prompted.

### 2b: Using the wallet

Now that we have the wallet created we can open it with nerva-wallet-rpc to start listening for incoming transactions. There are 2 ways to do this. Firstly is to start nervad and sync the blockchain. This is the preferred method as then you alone control all of the nerva systems required to receive payments. The other option is to use a public node. There are 2 considerations when using a public node. Firstly, there is some compromise in privacy. for example, a node operator can see the IP addresses of people making connections to their node. The primary consideration however is uptime. There is no guarantee a public node will be available at all times. It you lose connection to a public node, you will not be able to see incoming payments, which will cause disruption to customers and inconvenience to you. 

When you have decided which option you would like to use, simply start nerva-wallet-rpc with the appropriate flags

To start the wallet connecting to your own local node, you can use the following command  
`nerva-wallet-rpc --disable-rpc-login --rpc-bind-port <bind-port> --wallet-file <wallet-file-path> --prompt-for-password`  
where `<bind-port>` can be any valid port number, just so long as it matches what is set in the WooCommerce plugin. `<wallet-file-path>` is the path to the wallet file.  

The `--prompt-for-password` option will instruct the RPC wallet to ask you for your wallet password rather than setting it on the command line. If you were to use the `--password` flag instead, it could result in the cleartext password being stored on the system, such as in the .bash-history file on linux. This presents a major security concern, especially if using a regular wallet as opposed to a view-only wallet. 

If you decide to use a public node, the command is similar, with the inclusion of an additional flag to supply the node to connect to  
`nerva-wallet-rpc --disable-rpc-login --rpc-bind-port <bind-port> --daemon-address xnv.pubnodes.com --wallet-file <wallet-file-path> --prompt-for-password`

This will allow the RPC wallet to connect to the Nerva public node hosted at [pubnodes.com](https://www.pubnodes.com)

Now that you have the wallet started up, you are ready to set up the store and begin accepting payments in XNV.

## Step 3: Setup Nerva Gateway in WooCommerce

* Navigate to the "settings" panel in the WooCommerce widget in the WordPress admin panel.

* Click on "Checkout"

* Select "Nerva GateWay"

* Check the box labeled "Enable this payment gateway"

* Enter your nerva wallet address in the box labled "Nerva Address". If you do not know your address, you can run the `address` commmand in your nerva wallet

* Enter the IP address of your server in the box labeled "Nerva wallet rpc Host/IP".

* Enter the port number of the Wallet RPC in the box labeled "Nerva wallet rpc port"

Finally:

* Click on "Save changes"
